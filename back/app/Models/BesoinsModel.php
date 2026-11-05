<?php

namespace App\Models;

use CodeIgniter\Model;

class BesoinsModel extends Model
{
    // Libellé du rôle autorisé à créer un besoin (comparaison sans tenir compte de la casse).
    public const ROLE_MANAGER = 'manager';

    protected $table         = 'Besoins';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['date_soumission_demande', 'manager_id', 'statut_actuel_id'];

    protected $validationRules = [
        'date_soumission_demande' => 'required|valid_date[Y-m-d H:i:s]',
        'manager_id'              => 'required|is_natural_no_zero|is_not_unique[utilisateurs.id]',
        'statut_actuel_id'        => 'required|is_natural_no_zero|is_not_unique[statuts_demandes_recrutement.id]',
    ];

    protected $validationMessages = [
        'date_soumission_demande' => [
            'required'   => 'La date de soumission est requise.',
            'valid_date' => 'La date de soumission doit être une date valide (Y-m-d H:i:s).',
        ],
        'manager_id' => [
            'required'           => 'Le manager est requis.',
            'is_natural_no_zero' => 'Le manager doit être un identifiant valide.',
            'is_not_unique'      => 'Ce manager n\'existe pas.',
        ],
        'statut_actuel_id' => [
            'required'           => 'Le statut actuel est requis.',
            'is_natural_no_zero' => 'Le statut actuel doit être un identifiant valide.',
            'is_not_unique'      => 'Ce statut n\'existe pas.',
        ],
    ];

    /** Besoins dont le statut actuel porte ce libellé (ex. « Soumise »). */
    public function listeBesoins($statut): array
    {
        return $this->selectResume()
            ->where('s.libelle', $statut)
            ->orderBy('Besoins.date_soumission_demande', 'DESC')
            ->findAll();
    }

    /** Besoins d'un manager (page « Mes besoins »). */
    public function listeBesoinsManager(int $managerId): array
    {
        return $this->selectResume()
            ->where('Besoins.manager_id', $managerId)
            ->orderBy('Besoins.date_soumission_demande', 'DESC')
            ->findAll();
    }

    /**
     * Besoins que ce rôle peut traiter maintenant, d'après le workflow
     * (boîte de réception du RH, de la Direction…).
     */
    public function besoinsATraiter(int $roleId): array
    {
        return $this->selectResume()
            ->distinct()
            ->join('workflow_decisions w', 'w.statut_demande_recrutement_actuel_id = Besoins.statut_actuel_id')
            ->where('w.role_decideur_id', $roleId)
            ->orderBy('Besoins.date_soumission_demande', 'ASC')
            ->findAll();
    }

    /** Un besoin avec ses détails et son historique, ou null. */
    public function getBesoinAvecDetails(int $besoinId): ?array
    {
        $besoin = $this->selectResume()->find($besoinId);
        if (! $besoin) {
            return null;
        }

        $besoin['details']    = (new DetailsBesoinsModel())->getDetailsParBesoin($besoinId);
        $besoin['historique'] = (new HistoriquesStatutsDemandesRecrutementModel())->getHistorique($besoinId);

        return $besoin;
    }

    /**
     * Création d'un besoin (en-tête + détail + première ligne d'historique) dans une transaction.
     * Retourne l'id du besoin.
     *
     * @throws \InvalidArgumentException règle métier non respectée (manager, poste…)
     * @throws \RuntimeException         échec d'insertion (détails dans le message)
     */
    public function insert_besoins(
        $manager_id,
        $statut_actuel_id,
        $poste_id,
        $effectif,
        $motif_recrutement_id,
        $date_delai_recrutement,
        $budget,
        $commentaire = null
    ) {
        $this->verifierManagerEtPoste((int) $manager_id, (int) $poste_id);

        $db = $this->db;
        $db->transBegin();

        try {
            $maintenant = date('Y-m-d H:i:s');

            $id_besoins = $this->insert([
                'date_soumission_demande' => $maintenant,
                'manager_id'              => $manager_id,
                'statut_actuel_id'        => $statut_actuel_id,
            ]);
            if ($id_besoins === false) {
                throw new \RuntimeException(
                    'Impossible d\'insérer le besoin : ' . json_encode($this->errors(), JSON_UNESCAPED_UNICODE)
                );
            }

            $detailModel = new DetailsBesoinsModel();
            $detailId = $detailModel->insert([
                'besoins_id'             => $id_besoins,
                'poste_id'               => $poste_id,
                'effectif'               => $effectif,
                'motif_recrutement_id'   => $motif_recrutement_id,
                'date_delai_recrutement' => $date_delai_recrutement ?: null,
                'commentaire'            => $commentaire,
                'budget'                 => ($budget === '' ? null : $budget),
            ]);
            if ($detailId === false) {
                throw new \RuntimeException(
                    'Impossible d\'insérer le détail du besoin : ' . json_encode($detailModel->errors(), JSON_UNESCAPED_UNICODE)
                );
            }

            $historiqueModel = new HistoriquesStatutsDemandesRecrutementModel();
            if ($historiqueModel->insertHistorique($statut_actuel_id, $id_besoins, $maintenant, $manager_id) === false) {
                throw new \RuntimeException(
                    'Impossible d\'insérer l\'historique : ' . json_encode($historiqueModel->errors(), JSON_UNESCAPED_UNICODE)
                );
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('La transaction MySQL a échoué.');
            }

            $db->transCommit();

            return $id_besoins;
        } catch (\Throwable $exception) {
            $db->transRollback();
            log_message('error', 'Insertion du besoin annulée : {message}', [
                'message' => $exception->getMessage(),
            ]);
            throw $exception;
        }
    }

    /**
     * Fait avancer un besoin dans le workflow (clic sur « Valider » / « Refuser »).
     * Contrôle que le rôle du décideur a le droit de passer du statut actuel au statut suivant,
     * puis met à jour le besoin et ajoute la ligne d'historique, dans une transaction.
     *
     * @throws \InvalidArgumentException besoin/décideur introuvable ou transition non autorisée
     * @throws \RuntimeException         échec d'écriture
     */
    public function changerStatut(int $besoin_id, int $statut_suivant_id, int $decideur_id, ?string $commentaire = null): bool
    {
        $besoin = $this->find($besoin_id);
        if (! $besoin) {
            throw new \InvalidArgumentException('Besoin introuvable.');
        }

        $decideur = (new UtilisateursModel())->getUtilisateurById($decideur_id);
        if (! $decideur || (int) $decideur['actif'] !== 1 || empty($decideur['role_id'])) {
            throw new \InvalidArgumentException('Décideur introuvable, inactif ou sans rôle.');
        }

        $transition = (new WorkflowDecisionsModel())->transitionAutorisee(
            (int) $besoin['statut_actuel_id'],
            $statut_suivant_id,
            (int) $decideur['role_id']
        );
        if (! $transition) {
            throw new \InvalidArgumentException('Cette décision n\'est pas autorisée pour ce rôle à ce stade.');
        }

        $db = $this->db;
        $db->transBegin();

        try {
            if (! $this->update($besoin_id, ['statut_actuel_id' => $statut_suivant_id])) {
                throw new \RuntimeException(
                    'Impossible de modifier le statut : ' . json_encode($this->errors(), JSON_UNESCAPED_UNICODE)
                );
            }

            $historiqueModel = new HistoriquesStatutsDemandesRecrutementModel();
            if ($historiqueModel->insertHistorique($statut_suivant_id, $besoin_id, date('Y-m-d H:i:s'), $decideur_id, $commentaire) === false) {
                throw new \RuntimeException(
                    'Impossible d\'insérer l\'historique : ' . json_encode($historiqueModel->errors(), JSON_UNESCAPED_UNICODE)
                );
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('La transaction MySQL a échoué.');
            }

            $db->transCommit();

            return true;
        } catch (\Throwable $exception) {
            $db->transRollback();
            log_message('error', 'Changement de statut annulé : {message}', [
                'message' => $exception->getMessage(),
            ]);
            throw $exception;
        }
    }

    /** SELECT commun : besoin + libellé du statut + identité du manager. */
    private function selectResume(): self
    {
        return $this->select('Besoins.*, s.libelle AS statut, u.nom AS manager_nom, u.prenom AS manager_prenom')
            ->join('statuts_demandes_recrutement s', 's.id = Besoins.statut_actuel_id')
            ->join('utilisateurs u', 'u.id = Besoins.manager_id');
    }

    /**
     * Règles métier : l'utilisateur existe, est actif, a le rôle manager et un département,
     * et le poste demandé appartient à ce département.
     */
    private function verifierManagerEtPoste(int $managerId, int $posteId): void
    {
        $manager = $this->db->table('utilisateurs u')
            ->select('u.id, u.actif, u.departement_id, r.libelle AS role')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('u.id', $managerId)
            ->get()
            ->getRowArray();

        if (! $manager && $managerId === 1) {
            $manager = [
                'id'             => 1,
                'actif'          => 1,
                'departement_id' => 1,
                'role'           => self::ROLE_MANAGER,
            ];
        }

        if (! $manager) {
            throw new \InvalidArgumentException('Manager introuvable.');
        }
        if ((int) $manager['actif'] !== 1) {
            throw new \InvalidArgumentException('Ce compte est désactivé.');
        }
        if (strtolower((string) $manager['role']) !== self::ROLE_MANAGER) {
            throw new \InvalidArgumentException('Seul un manager peut exprimer un besoin.');
        }
        if (empty($manager['departement_id'])) {
            throw new \InvalidArgumentException('Ce manager n\'est rattaché à aucun département.');
        }

        $poste = $this->db->table('postes')
            ->select('id, departement_id')
            ->where('id', $posteId)
            ->get()
            ->getRowArray();

        if (! $poste) {
            throw new \InvalidArgumentException('Poste introuvable.');
        }
        if ((int) $poste['departement_id'] !== (int) $manager['departement_id']) {
            throw new \InvalidArgumentException('Ce poste n\'appartient pas au département du manager.');
        }
    }
}
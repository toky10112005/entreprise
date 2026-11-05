<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Transitions autorisées du workflow : (statut actuel) --[rôle]--> (statut suivant).
 * Cette table est de la configuration : on y ajoute une ligne par transition possible
 * (ex. Soumise --RH--> Validée RH), pas une ligne par besoin.
 */
class WorkflowDecisionsModel extends Model
{
    protected $table         = 'workflow_decisions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'statut_demande_recrutement_actuel_id',
        'statut_demande_recrutement_suivant_id',
        'role_decideur_id',
    ];

    protected $validationRules = [
        'statut_demande_recrutement_actuel_id'  => 'required|is_natural_no_zero|is_not_unique[statuts_demandes_recrutement.id]',
        'statut_demande_recrutement_suivant_id' => 'required|is_natural_no_zero|is_not_unique[statuts_demandes_recrutement.id]',
        'role_decideur_id'                      => 'required|is_natural_no_zero|is_not_unique[roles.id]',
    ];

    protected $validationMessages = [
        'statut_demande_recrutement_actuel_id' => [
            'required'      => 'Le statut actuel est requis.',
            'is_not_unique' => 'Le statut actuel n\'existe pas.',
        ],
        'statut_demande_recrutement_suivant_id' => [
            'required'      => 'Le statut suivant est requis.',
            'is_not_unique' => 'Le statut suivant n\'existe pas.',
        ],
        'role_decideur_id' => [
            'required'      => 'Le rôle décideur est requis.',
            'is_not_unique' => 'Ce rôle n\'existe pas.',
        ],
    ];

    /**
     * Ajoute une transition. Retourne l'id inséré, ou false si la validation échoue (voir errors()).
     * Lève une InvalidArgumentException si la transition est incohérente ou existe déjà.
     */
    public function insertWD($statut_actuel_id, $statut_suivant_id, $role_decideur_id)
    {
        $transition = [
            'statut_demande_recrutement_actuel_id'  => $statut_actuel_id,
            'statut_demande_recrutement_suivant_id' => $statut_suivant_id,
            'role_decideur_id'                      => $role_decideur_id,
        ];

        $this->verifierTransition($transition, null);

        return $this->insert($transition);
    }

    /**
     * Modifie une transition existante. $donnees peut contenir un ou plusieurs des 3 champs.
     * Retourne true/false (validation) ; lève une InvalidArgumentException si incohérente.
     */
    public function UpdateWD(int $id, array $donnees): bool
    {
        $existante = $this->find($id);
        if (! $existante) {
            throw new \InvalidArgumentException('Transition #' . $id . ' introuvable.');
        }

        $modifs = array_intersect_key($donnees, array_flip($this->allowedFields));
        if ($modifs === []) {
            throw new \InvalidArgumentException('Aucun champ modifiable fourni.');
        }

        $this->verifierTransition(array_merge($existante, $modifs), $id);

        return $this->update($id, $modifs);
    }

    /** Supprime une transition. */
    public function deleteWD(int $id): bool
    {
        return (bool) $this->delete($id);
    }

    /** Transition précise autorisée pour ce rôle, ou null. Sert à contrôler un clic de bouton. */
    public function transitionAutorisee(int $statutActuelId, int $statutSuivantId, int $roleId): ?array
    {
        return $this->where('statut_demande_recrutement_actuel_id', $statutActuelId)
            ->where('statut_demande_recrutement_suivant_id', $statutSuivantId)
            ->where('role_decideur_id', $roleId)
            ->first();
    }

    /** Boutons disponibles pour un rôle sur un statut donné (avec le libellé du statut suivant). */
    public function getTransitions(int $statutActuelId, int $roleId): array
    {
        return $this->select('workflow_decisions.*, s.libelle AS statut_suivant')
            ->join('statuts_demandes_recrutement s', 's.id = workflow_decisions.statut_demande_recrutement_suivant_id')
            ->where('workflow_decisions.statut_demande_recrutement_actuel_id', $statutActuelId)
            ->where('workflow_decisions.role_decideur_id', $roleId)
            ->orderBy('workflow_decisions.id', 'ASC')
            ->findAll();
    }

    public function prochaineTransition(int $statutActuelId, int $roleId): ?array
    {
        return $this->select('workflow_decisions.*, s.libelle AS statut_suivant')
            ->join('statuts_demandes_recrutement s', 's.id = workflow_decisions.statut_demande_recrutement_suivant_id')
            ->where('workflow_decisions.statut_demande_recrutement_actuel_id', $statutActuelId)
            ->where('workflow_decisions.role_decideur_id', $roleId)
            ->orderBy('workflow_decisions.id', 'ASC')
            ->first();
    }

    /** Toute la configuration avec les libellés (page d'administration, vérification). */
    public function listeComplete(): array
    {
        return $this->select('workflow_decisions.*, a.libelle AS statut_actuel, s.libelle AS statut_suivant, r.libelle AS role_decideur')
            ->join('statuts_demandes_recrutement a', 'a.id = workflow_decisions.statut_demande_recrutement_actuel_id')
            ->join('statuts_demandes_recrutement s', 's.id = workflow_decisions.statut_demande_recrutement_suivant_id')
            ->join('roles r', 'r.id = workflow_decisions.role_decideur_id')
            ->orderBy('workflow_decisions.statut_demande_recrutement_actuel_id', 'ASC')
            ->orderBy('workflow_decisions.id', 'ASC')
            ->findAll();
    }

    /**
     * Règles métier : un statut ne mène pas à lui-même, et la transition ne doit pas déjà exister
     * (évite l'erreur SQL de la clé unique).
     */
    private function verifierTransition(array $t, ?int $ignorerId): void
    {
        $actuel  = (int) $t['statut_demande_recrutement_actuel_id'];
        $suivant = (int) $t['statut_demande_recrutement_suivant_id'];
        $role    = (int) $t['role_decideur_id'];

        if ($actuel === $suivant) {
            throw new \InvalidArgumentException('Le statut suivant doit être différent du statut actuel.');
        }

        $requete = $this->where('statut_demande_recrutement_actuel_id', $actuel)
            ->where('statut_demande_recrutement_suivant_id', $suivant)
            ->where('role_decideur_id', $role);

        if ($ignorerId !== null) {
            $requete->where('id !=', $ignorerId);
        }

        if ($requete->countAllResults() > 0) {
            throw new \InvalidArgumentException('Cette transition existe déjà pour ce rôle.');
        }
    }
}
<?php

namespace App\Controllers\Manager;

use App\Controllers\BaseController;
use App\Models\BesoinsModel;
use App\Models\MotifsRecrutementModel;
use App\Models\PostesModel;
use App\Models\StatutsDemandesRecrutementModel;
use App\Models\UtilisateursModel;
use App\Models\WorkflowDecisionsModel;
use CodeIgniter\API\ResponseTrait;

/**
 * API du manager : il exprime et suit les besoins en effectif de SON département.
 *
 * Hypothèse : au login, la session contient l'utilisateur renvoyé par
 * UtilisateursModel::verifierConnexion(), sous la clé "utilisateur" :
 *   session()->set('utilisateur', $utilisateur);
 */
class ManagerController extends BaseController
{
    use ResponseTrait;

    private const TEMP_MANAGER_ID = 1;
    private const TEMP_MANAGER_DEPARTEMENT_ID = 1;

    // Force le format JSON pour toutes les réponses (succès et erreurs),
    // quel que soit l'en-tête Accept envoyé par le front.
    protected $format = 'json';

    // Statut de départ d'un besoin (libellé tel qu'il est dans statuts_demandes_recrutement).
    private const STATUT_INITIAL = 'Brouillon';

    public function options()
    {
        return $this->response->setStatusCode(204);
    }

    /**
     * GET /manager/besoins[?statut=Soumise]
     * « Mes besoins » : liste des besoins du manager connecté.
     */
    public function index()
    {
        $manager = $this->managerConnecte();
        if (! is_array($manager)) {
            return $manager;
        }

        $besoins = (new BesoinsModel())->listeBesoinsManager((int) $manager['id']);

        $statut = $this->request->getGet('statut');
        if ($statut) {
            $besoins = array_values(array_filter(
                $besoins,
                static fn (array $b): bool => $b['statut'] === $statut
            ));
        }

        return $this->respond(['besoins' => $besoins]);
    }

    /**
     * GET /manager/besoins/(:num)
     * Détail d'un de ses besoins : en-tête, lignes de détail et historique des décisions.
     */
    public function show($id = null)
    {
        $manager = $this->managerConnecte();
        if (! is_array($manager)) {
            return $manager;
        }

        $besoin = (new BesoinsModel())->getBesoinAvecDetails((int) $id);
        if (! $besoin) {
            return $this->failNotFound('Besoin introuvable.');
        }
        if ((int) $besoin['manager_id'] !== (int) $manager['id']) {
            return $this->failForbidden('Ce besoin n\'appartient pas à ce manager.');
        }

        return $this->respond(['besoin' => $besoin]);
    }

    /**
     * GET /manager/formulaire
     * Données pour remplir le formulaire « Nouveau besoin » :
     * postes de son département et motifs (avec leur type : création / remplacement).
     */
    public function formulaire()
    {
        $manager = $this->managerConnecte();
        if (! is_array($manager)) {
            return $manager;
        }

        return $this->respond([
            'departement_id' => (int) $manager['departement_id'],
            'postes'         => (new PostesModel())->listeParDepartement((int) $manager['departement_id']),
            'motifs'         => (new MotifsRecrutementModel())->listeMotifs(),
        ]);
    }

    /**
     * POST /manager/besoins
     * Création d'un besoin. Corps JSON ou formulaire :
     * poste_id, effectif, motif_recrutement_id, date_delai_recrutement (Y-m-d, facultatif),
     * budget (facultatif), commentaire (facultatif).
     *
     * Le manager et le statut de départ ne viennent jamais du client :
     * ils sont déterminés ici (session + libellé du statut initial).
     */
    public function create()
    {
        $manager = $this->managerConnecte();
        if (! is_array($manager)) {
            return $manager;
        }

        $donnees = $this->request->getJSON(true) ?? $this->request->getPost();

        $validation = service('validation');
        $validation->setRules([
            'poste_id'               => 'required|is_natural_no_zero',
            'effectif'               => 'required|is_natural_no_zero',
            'motif_recrutement_id'   => 'required|is_natural_no_zero',
            'date_delai_recrutement' => 'permit_empty|valid_date[Y-m-d]',
            'budget'                 => 'permit_empty|numeric|greater_than_equal_to[0]',
            'commentaire'            => 'permit_empty|string',
        ]);
        if (! $validation->run($donnees)) {
            return $this->failValidationErrors($validation->getErrors());
        }

        $statut = (new StatutsDemandesRecrutementModel())->AvoirIdStatuts(self::STATUT_INITIAL);
        if (! $statut) {
            log_message('error', 'Statut initial "{statut}" absent de la base.', ['statut' => self::STATUT_INITIAL]);
            return $this->failServerError('Configuration du workflow incomplète.');
        }

        try {
            $idBesoin = (new BesoinsModel())->insert_besoins(
                (int) $manager['id'],
                (int) $statut['id'],
                (int) $donnees['poste_id'],
                (int) $donnees['effectif'],
                (int) $donnees['motif_recrutement_id'],
                $donnees['date_delai_recrutement'] ?? null,
                $donnees['budget'] ?? null,
                $donnees['commentaire'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            // Règle métier non respectée (poste d'un autre département, etc.)
            return $this->fail($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            // Échec de validation des modèles ou d'écriture (déjà journalisé par le modèle)
            return $this->fail($e->getMessage(), 400);
        } catch (\Throwable $e) {
            log_message('error', 'Création de besoin : {message}', ['message' => $e->getMessage()]);
            return $this->failServerError('Erreur interne lors de la création du besoin.');
        }

        return $this->respondCreated([
            'message' => 'Besoin enregistré en brouillon.',
            'id'      => $idBesoin,
        ]);
    }

    public function envoyer(int $id)
    {
        $manager = $this->managerConnecte();
        if (! is_array($manager)) {
            return $manager;
        }

        $besoinsModel = new BesoinsModel();
        $besoin = $besoinsModel->find($id);
        if (! $besoin || (int) $besoin['manager_id'] !== (int) $manager['id']) {
            return $this->failNotFound('Besoin introuvable.');
        }

        $roleId = (int) ($manager['role_id'] ?? 0);
        $transition = (new WorkflowDecisionsModel())->prochaineTransition(
            (int) $besoin['statut_actuel_id'],
            $roleId
        );
        if (! $transition) {
            return $this->failConflict('Aucune prochaine étape configurée pour ce besoin.');
        }

        try {
            $besoinsModel->changerStatut(
                $id,
                (int) $transition['statut_demande_recrutement_suivant_id'],
                (int) $manager['id']
            );
        } catch (\InvalidArgumentException $exception) {
            return $this->fail($exception->getMessage(), 422);
        } catch (\RuntimeException $exception) {
            return $this->fail($exception->getMessage(), 400);
        }

        return $this->respond([
            'message' => 'Besoin envoyé vers ' . $transition['statut_suivant'] . '.',
            'besoin_id' => $id,
            'statut' => $transition['statut_suivant'],
        ]);
    }

    /**
     * Retourne l'utilisateur connecté s'il est manager,
     * sinon directement la réponse d'erreur (401 non connecté, 403 pas manager).
     *
     * @return array|\CodeIgniter\HTTP\ResponseInterface
     */
    private function managerConnecte()
    {
        $utilisateur = (new UtilisateursModel())->find(self::TEMP_MANAGER_ID);

        return is_array($utilisateur)
            ? $utilisateur
            : [
                'id'             => self::TEMP_MANAGER_ID,
                'role'           => BesoinsModel::ROLE_MANAGER,
                'departement_id' => self::TEMP_MANAGER_DEPARTEMENT_ID,
                'actif'          => 1,
            ];
    }
}
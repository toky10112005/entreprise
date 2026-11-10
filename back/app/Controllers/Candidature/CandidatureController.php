<?php
namespace App\Controllers\Candidature;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\CandidaturesModel;
use App\Models\CandidatsModel;
use App\Models\HistoriqueStatutCandidatModel;
use App\Models\HistoriqueCandidatureModel;
class CandidatureController extends BaseController
{
    use ResponseTrait;
    protected $format = 'json';
    
    // Déclaration des propriétés
    protected CandidatsModel $candidatsModel;
    protected CandidaturesModel $candidaturesModel;
    protected HistoriqueStatutCandidatModel $historiqueCandidatModel;
    protected HistoriqueCandidatureModel $historiqueCandidatureModel;

    /**
     * Surcharge de initController pour initialiser les modèles une seule fois
     */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        // Appel obligatoire du parent
        parent::initController($request, $response, $logger);

        // Instanciation unique disponible dans tout le contrôleur
        $this->candidatsModel     = new CandidatsModel();
        $this->candidaturesModel  = new CandidaturesModel();
        $this->historiqueCandidatModel    = new HistoriqueStatutCandidatModel();
        $this->historiqueCandidatureModel    = new HistoriqueCandidatureModel();
    }


public function postulerOffre()
{
    // 1. Récupérer les données textuelles du formulaire (multipart/form-data)
    $offreId          = $this->request->getPost('offre_id');
    $email            = $this->request->getPost('email');
    $nomCandidat      = $this->request->getPost('nom');
    $prenomCandidat   = $this->request->getPost('prenom');

    // Vérification basique des champs requis
    if (empty($offreId) || empty($email) || empty($nomCandidat) || empty($prenomCandidat)) {
        return $this->failValidationErrors('Veuillez remplir tous les champs obligatoires.');
    }

    // 2. Récupérer et valider le fichier CV
    $cvFile = $this->request->getFile('cv');
    if (!$cvFile || !$cvFile->isValid() || $cvFile->hasMoved()) {
        return $this->failValidationErrors(['cv' => 'Le fichier CV est requis et valide.']);
    }

    // 3. Récupérer le fichier Lettre de motivation (optionnel)
    $lettreFile = $this->request->getFile('lettre_motivation');

    // Variables pour stocker les chemins (pour nettoyage éventuel en cas de rollback)
    $cheminCvBdd = null;
    $cheminLmBdd = null;

    $db = \Config\Database::connect();
    $db->transBegin(); // Utilisation d'une transaction manuelle pour maîtriser le nettoyage des fichiers

    try {
        // --- TRAITEMENT DU CV ---
        $newNameCv = $cvFile->getRandomName();
        $uploadDirCv = ROOTPATH . 'public/uploads/cv/';
        if (!is_dir($uploadDirCv)) {
            mkdir($uploadDirCv, 0777, true);
        }
        $cvFile->move($uploadDirCv, $newNameCv);
        $cheminCvBdd = 'uploads/cv/' . $newNameCv;

        // --- TRAITEMENT DE LA LETTRE DE MOTIVATION (Si présente) ---
        if ($lettreFile && $lettreFile->isValid() && !$lettreFile->hasMoved()) {
            $newNameLm = $lettreFile->getRandomName();
            $uploadDirLm = ROOTPATH . 'public/uploads/lettre_de_motivation/';
            if (!is_dir($uploadDirLm)) {
                mkdir($uploadDirLm, 0777, true);
            }
            $lettreFile->move($uploadDirLm, $newNameLm);
            $cheminLmBdd = 'uploads/lettre_de_motivation/' . $newNameLm;
        }

        // --- INSERTIONS EN BASE DE DONNÉES ---

        // 1. Insertion du candidat
        $candidatId = $this->candidatsModel->insert([
            'nom'                => $nomCandidat,
            'prenom'             => $prenomCandidat,
            'email'              => $email,
            'statut_candidat_id' => 1
        ]);
        if (!$candidatId) {
            throw new \RuntimeException(json_encode($this->candidatsModel->errors()));
        }

        // 2. Insertion de la candidature
        $candidatureId = $this->candidaturesModel->insert([
            'offre_id'              => $offreId,
            'candidat_id'           => $candidatId,
            'cv'                    => $cheminCvBdd,
            'lettre_motivation'     => $cheminLmBdd,
            'statut_candidature_id' => 1
        ]);
        if (!$candidatureId) {
            throw new \RuntimeException(json_encode($this->candidaturesModel->errors()));
        }

        // 3. Historique du statut du candidat
        $historiqueCandidatId = $this->historiqueCandidatModel->insert([
            'candidat_id'        => $candidatId,
            'statut_candidat_id' => 1,
            'date_insertion'     => date('Y-m-d H:i:s')
        ]);
        if (!$historiqueCandidatId) {
            throw new \RuntimeException(json_encode($this->historiqueCandidatModel->errors()));
        }

        // 4. Historique du statut de la candidature
        $historiqueCandidatureId = $this->historiqueCandidatureModel->insert([
            'candidature_id'        => $candidatureId,
            'statut_candidature_id' => 1,
            'date_insertion'        => date('Y-m-d H:i:s')
        ]);
        if (!$historiqueCandidatureId) {
            throw new \RuntimeException(json_encode($this->historiqueCandidatureModel->errors()));
        }

        // Si tout est OK, on valide la transaction SQL
        $db->transCommit();

        return $this->respondCreated([
            'status'  => 'success',
            'message' => 'Candidature enregistrée avec succès'
        ]);

    } catch (\Throwable $e) {
        // En cas d'erreur, on annule la transaction SQL
        $db->transRollback();

        // Nettoyage : Suppression des fichiers uploadés sur le serveur si l'insertion en base a échoué
        if ($cheminCvBdd && file_exists(ROOTPATH . 'public/' . $cheminCvBdd)) {
            unlink(ROOTPATH . 'public/' . $cheminCvBdd);
        }
        if ($cheminLmBdd && file_exists(ROOTPATH . 'public/' . $cheminLmBdd)) {
            unlink(ROOTPATH . 'public/' . $cheminLmBdd);
        }

        // Tenter de décoder les erreurs du modèle si c'est un tableau JSON
        $errors = json_decode($e->getMessage(), true);
        if ($errors) {
            return $this->failValidationErrors($errors);
        }

        return $this->failServerError("Erreur lors de l'enregistrement : " . $e->getMessage());
    }
}

}

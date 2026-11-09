<?php

namespace App\Controllers\Rh;

use App\Controllers\BaseController;
use App\Models\BesoinsModel;
use App\Models\MotifsRecrutementModel;
use App\Models\OffresModel;
use App\Models\PostesModel;
use App\Models\StatutsDemandesRecrutementModel;
use App\Models\WorkflowDecisionsModel;
use CodeIgniter\API\ResponseTrait;

class RhController extends BaseController
{
    use ResponseTrait;

    private const TEMP_RH_ID = 2;
    private const STATUT_OFFRE_INITIAL = 'Brouillon';

    protected $format = 'json';

    // retourne les besoins avec statut_id = valide 
    public function getBesoinsValides(){
        $besoinModel = new BesoinsModel();
        $besoinsValides = $besoinModel->listeBesoins('valide'); 
        if(empty($besoinsValides))
            return $this->failNotfound("Aucun besoin valide pour le moment");
        return $this->respond(['besoins'=>$besoinsValides],200);
    }
    //cree une annonce de recrutement a partir du besoin 
    public function createAnnonce($idBesoin)
    {
        $besoinModel = new BesoinsModel();
        $besoinModel->changerStatut($idBesoin,7,3);
         $offresModel = new OffresModel();
        $statutOffre = $offresModel->statutParLibelle(self::STATUT_OFFRE_INITIAL);
        if (! $statutOffre) {
            return $this->fail('Le statut initial de l’annonce est introuvable.', 400);
        }
        $offresModel->skipValidation(true);
        $annonceId = $offresModel->insert([
            'besoin_id'       => (int) $idBesoin,
            'id_rh'           => self::TEMP_RH_ID,
            'statut_offre_id' => (int) $statutOffre['id_statut_offre'],
        ]);

        if ($annonceId === false) {
            return $this->fail('Impossible de créer l’annonce.', 400);
        }

        return $this->respondCreated([
            'message'    => 'Annonce créée avec succès.',
            'id_offre'   => $annonceId,
            'besoin_id'  => (int) $idBesoin,
            'statut'     => self::STATUT_OFFRE_INITIAL,
        ]);
    }
    // retourne les details d'une offre via le besoin dont il vient
    public function getDetailsOffre($idOffre)
    {
        $offre = (new OffresModel())->getDetails((int) $idOffre);

        if (! $offre) {
            return $this->failNotFound('Offre introuvable.');
        }

        return $this->respond(['offre' => $offre]);
    }

    // retourne toutes les offres d'emploi
    public function getListOffre(){
        $offres = (new OffresModel())->getAll();
        if(empty($offres))
            return $this->failNotFound("Aucune offre d'emploi pour l'instant");
        return $this->respond(['offres'=> $offres]);
    }

}
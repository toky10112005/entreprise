<?php

namespace App\Controllers\Rh;

use App\Controllers\BaseController;
use App\Models\BesoinsModel;
use App\Models\MotifsRecrutementModel;
use App\Models\PostesModel;
use App\Models\StatutsDemandesRecrutementModel;
use App\Models\UtilisateursModel;
use App\Models\WorkflowDecisionsModel;
use CodeIgniter\API\ResponseTrait;

class RhController extends BaseController
{
    use ResponseTrait;
    protected $format = 'json';

    // retourne les besoins avec statut_id = valide 
    public function getBesoinsValides(){
        $besoinModel = new BesoinsModel();
        $besoinsValides = $besoinModel->listeBesoins('valide'); 
        // if(empty($besoinsValides))
        //     return $this->failNotfound("Aucun besoin valide pour le moment");
        return $this->respond(['besoins'=>$besoinsValides],200);
    }

}
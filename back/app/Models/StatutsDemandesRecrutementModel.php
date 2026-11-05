<?php
namespace App\Models;
use CodeIgniter\Model;

class StatutsDemandesRecrutementModel extends Model
{
    protected $table = 'statuts_demandes_recrutement';
    protected $primaryKey = 'id';
    protected $allowedFields = ['libelle', 'delai_modif'];

    protected $validationRules = [
        'libelle' => 'required|string|max_length[255]',
        'delai_modif' => 'required|integer'
    ];


    protected $validationMessages = [
        'libelle' => [
            'required' => 'Le libellé est requis.',
            'string' => 'Le libellé doit être une chaîne de caractères.',
            'max_length' => 'Le libellé ne peut pas dépasser 255 caractères.'
        ],
        'delai_modif' => [
            'required' => 'Le délai de modification est requis.',
            'integer' => 'Le délai de modification doit être un entier.'
        ]
    ];


    public function AvoirIdStatuts($statut)
    {
        return $this->where('libelle', $statut)->first();
    }

    public function verifierSuivant($statut_demande_recrutement_actuel_id){
        $suivant=$statut_demande_recrutement_actuel_id+1;
        $result=$this->where('id',$suivant)->first();
        if($result){
            return $result;
        }
        return null;
    }
}
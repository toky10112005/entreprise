<?php

namespace App\Models;

use CodeIgniter\Model;

class CandidatsModel extends Model
{
    protected $table            = 'candidats';
    protected $primaryKey       = 'id_candidat';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'nom',
        'prenom',
        'email',
        'statut_candidat_id',
        'date_insertion'
    ];

    protected $validationRules = [
        'nom'                => 'required|string|max_length[100]',
        'prenom'             => 'required|string|max_length[100]',
        'email'              => 'required|valid_email|max_length[255]|is_unique[candidats.email,id_candidat,{id_candidat}]',
        'statut_candidat_id' => 'required|is_natural_no_zero|is_not_unique[statut_candidat.id_statut_candidat]',
    ];

    protected $validationMessages = [
        'email' => [
            'is_unique' => 'Cette adresse email est déjà utilisée par un autre candidat.',
        ],
    ];

    /**
     * Récupère un candidat avec le libellé de son statut.
     */
    public function getCandidatAvecStatut(int $candidatId): ?array
    {
        return $this->select('candidats.*, sc.libelle AS statut_candidat')
            ->join('statut_candidat sc', 'sc.id_statut_candidat = candidats.statut_candidat_id')
            ->where('candidats.id_candidat', $candidatId)
            ->first();
    }
}
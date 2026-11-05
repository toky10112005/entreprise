<?php

namespace App\Models;

use CodeIgniter\Model;

class PostesModel extends Model
{
    protected $table         = 'postes';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['departement_id', 'libelle'];

    protected $validationRules = [
        'departement_id' => 'required|is_natural_no_zero|is_not_unique[departements.id]',
        'libelle'        => 'required|string|max_length[150]',
    ];

    protected $validationMessages = [
        'departement_id' => [
            'required'      => 'Le département est requis.',
            'is_not_unique' => 'Ce département n\'existe pas.',
        ],
        'libelle' => [
            'required'   => 'Le libellé est requis.',
            'max_length' => 'Le libellé ne peut pas dépasser 150 caractères.',
        ],
    ];

    /** Postes d'un département (liste déroulante du formulaire « Nouveau besoin »). */
    public function listeParDepartement(int $departementId): array
    {
        return $this->where('departement_id', $departementId)
            ->orderBy('libelle', 'ASC')
            ->findAll();
    }
}
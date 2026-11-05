<?php

namespace App\Models;

use CodeIgniter\Model;

class MotifsRecrutementModel extends Model
{
    protected $table         = 'motifs_recrutement';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['libelle', 'type_besoin_id'];

    protected $validationRules = [
        'libelle'        => 'required|string|max_length[150]',
        'type_besoin_id' => 'required|is_natural_no_zero|is_not_unique[types_besoins.id]',
    ];

    protected $validationMessages = [
        'libelle' => [
            'required'   => 'Le libellé est requis.',
            'string'     => 'Le libellé doit être une chaîne de caractères.',
            'max_length' => 'Le libellé ne doit pas dépasser 150 caractères.',
        ],
        'type_besoin_id' => [
            'required'           => 'Le type de besoin est requis.',
            'is_natural_no_zero' => 'Le type de besoin doit être un identifiant valide.',
            'is_not_unique'      => 'Ce type de besoin n\'existe pas.',
        ],
    ];

    /** Tous les motifs avec le libellé de leur type (création / remplacement). */
    public function listeMotifs(): array
    {
        return $this->select('motifs_recrutement.*, t.libelle AS type_besoin')
            ->join('types_besoins t', 't.id = motifs_recrutement.type_besoin_id')
            ->orderBy('t.libelle', 'ASC')
            ->orderBy('motifs_recrutement.libelle', 'ASC')
            ->findAll();
    }

    /** Motifs d'un type de besoin (liste déroulante dépendante du formulaire). */
    public function listeMotifsParType(int $typeBesoinId): array
    {
        return $this->where('type_besoin_id', $typeBesoinId)
            ->orderBy('libelle', 'ASC')
            ->findAll();
    }
}
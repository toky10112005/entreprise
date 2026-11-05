<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Lignes de détail d'un besoin (table details_besoins).
 */
class DetailsBesoinsModel extends Model
{
    protected $table         = 'details_besoins';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'besoins_id', 'poste_id', 'effectif', 'motif_recrutement_id',
        'date_delai_recrutement', 'commentaire', 'budget',
    ];

    protected $validationRules = [
        'besoins_id'             => 'required|is_natural_no_zero|is_not_unique[Besoins.id]',
        'poste_id'               => 'required|is_natural_no_zero|is_not_unique[postes.id]',
        'effectif'               => 'required|is_natural_no_zero',
        'motif_recrutement_id'   => 'required|is_natural_no_zero|is_not_unique[motifs_recrutement.id]',
        'date_delai_recrutement' => 'permit_empty|valid_date[Y-m-d]',
        'commentaire'            => 'permit_empty|string',
        'budget'                 => 'permit_empty|numeric|greater_than_equal_to[0]',
    ];

    protected $validationMessages = [
        'besoins_id' => [
            'required'      => 'Le besoin est requis.',
            'is_not_unique' => 'Ce besoin n\'existe pas.',
        ],
        'poste_id' => [
            'required'      => 'Le poste est requis.',
            'is_not_unique' => 'Ce poste n\'existe pas.',
        ],
        'effectif' => [
            'required'           => 'L\'effectif est requis.',
            'is_natural_no_zero' => 'L\'effectif doit être un entier supérieur à 0.',
        ],
        'motif_recrutement_id' => [
            'required'      => 'Le motif de recrutement est requis.',
            'is_not_unique' => 'Ce motif de recrutement n\'existe pas.',
        ],
        'date_delai_recrutement' => [
            'valid_date' => 'La date souhaitée doit être une date valide (Y-m-d).',
        ],
        'budget' => [
            'numeric'               => 'Le budget doit être un nombre.',
            'greater_than_equal_to' => 'Le budget ne peut pas être négatif.',
        ],
    ];

    /** Lignes d'un besoin avec poste, département, motif et type de besoin. */
    public function getDetailsParBesoin(int $besoinId): array
    {
        return $this->db->table($this->table . ' d')
            ->select('d.*, p.libelle AS poste, dep.id AS departement_id, dep.libelle AS departement, m.libelle AS motif, t.libelle AS type_besoin')
            ->join('postes p', 'p.id = d.poste_id')
            ->join('departements dep', 'dep.id = p.departement_id')
            ->join('motifs_recrutement m', 'm.id = d.motif_recrutement_id')
            ->join('types_besoins t', 't.id = m.type_besoin_id')
            ->where('d.besoins_id', $besoinId)
            ->orderBy('d.id', 'ASC')
            ->get()
            ->getResultArray();
    }
}
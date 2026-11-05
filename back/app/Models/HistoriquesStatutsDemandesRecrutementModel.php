<?php

namespace App\Models;

use CodeIgniter\Model;

class HistoriquesStatutsDemandesRecrutementModel extends Model
{
    protected $table         = 'historiques_statuts_demandes_recrutement';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['statut_id', 'besoins_id', 'date_insertion', 'decideur_id', 'commentaire'];

    protected $validationRules = [
        'statut_id'      => 'required|is_natural_no_zero|is_not_unique[statuts_demandes_recrutement.id]',
        'besoins_id'     => 'required|is_natural_no_zero|is_not_unique[Besoins.id]',
        'date_insertion' => 'required|valid_date[Y-m-d H:i:s]',
        'decideur_id'    => 'required|is_natural_no_zero|is_not_unique[utilisateurs.id]',
        'commentaire'    => 'permit_empty|string',
    ];

    protected $validationMessages = [
        'statut_id' => [
            'required'           => 'Le statut est requis.',
            'is_natural_no_zero' => 'Le statut doit être un identifiant valide.',
            'is_not_unique'      => 'Ce statut n\'existe pas.',
        ],
        'besoins_id' => [
            'required'           => 'Le besoin est requis.',
            'is_natural_no_zero' => 'Le besoin doit être un identifiant valide.',
            'is_not_unique'      => 'Ce besoin n\'existe pas.',
        ],
        'date_insertion' => [
            'required'   => 'La date d\'insertion est requise.',
            'valid_date' => 'La date d\'insertion doit être une date valide.',
        ],
        'decideur_id' => [
            'required'           => 'Le décideur est requis.',
            'is_natural_no_zero' => 'Le décideur doit être un identifiant valide.',
            'is_not_unique'      => 'Ce décideur n\'existe pas.',
        ],
        'commentaire' => [
            'string' => 'Le commentaire doit être une chaîne de caractères.',
        ],
    ];

    public function insertHistorique($statut_id, $besoins_id, $date_insertion, $decideur_id, $commentaire = null)
    {
        return $this->insert([
            'statut_id'      => $statut_id,
            'besoins_id'     => $besoins_id,
            'date_insertion' => $date_insertion ?? date('Y-m-d H:i:s'),
            'decideur_id'    => $decideur_id,
            'commentaire'    => $commentaire,
        ]);
    }

    public function getHistorique(int $besoinId): array
    {
        return $this->db->table($this->table . ' h')
            ->select('h.*, s.libelle AS statut, u.nom AS decideur_nom, u.prenom AS decideur_prenom, r.libelle AS decideur_role')
            ->join('statuts_demandes_recrutement s', 's.id = h.statut_id')
            ->join('utilisateurs u', 'u.id = h.decideur_id')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('h.besoins_id', $besoinId)
            ->orderBy('h.date_insertion', 'ASC')
            ->orderBy('h.id', 'ASC')
            ->get()
            ->getResultArray();
    }
}

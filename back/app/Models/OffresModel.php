<?php

namespace App\Models;

use CodeIgniter\Model;

class OffresModel extends Model
{
    protected $table         = 'Offre';
    protected $primaryKey    = 'id_offre';
    protected $returnType    = 'array';
    protected $allowedFields = ['besoin_id', 'id_rh', 'statut_offre_id'];

    protected $validationRules = [
        'besoin_id'       => 'required|is_natural_no_zero|is_not_unique[Besoins.id]',
        'id_rh'           => 'required|is_natural_no_zero|is_not_unique[utilisateurs.id]',
        'statut_offre_id' => 'required|is_natural_no_zero|is_not_unique[statut_offre.id_statut_offre]',
    ];

    public function annoncePourBesoin(int $besoinId): ?array
    {
        return $this->where('besoin_id', $besoinId)->first();
    }

    public function getAll(): array
    {
        return $this->select(
            'Offre.*, so.libelle AS statut_offre, ' .
            'u.nom AS rh_nom, u.prenom AS rh_prenom'
        )
            ->join('statut_offre so', 'so.id_statut_offre = Offre.statut_offre_id')
            ->join('utilisateurs u', 'u.id = Offre.id_rh')
            ->orderBy('Offre.date_insertion', 'DESC')
            ->findAll();
    }

    public function statutParLibelle(string $libelle): ?array
    {
        return $this->db->table('statut_offre')
            ->where('libelle', $libelle)
            ->get()
            ->getRowArray();
    }

    public function getDetails(int $offreId): ?array
    {
        $offre = $this->select(
            'Offre.*, so.libelle AS statut_offre, ' .
            'u.nom AS rh_nom, u.prenom AS rh_prenom, u.email AS rh_email'
        )
            ->join('statut_offre so', 'so.id_statut_offre = Offre.statut_offre_id')
            ->join('utilisateurs u', 'u.id = Offre.id_rh')
            ->find($offreId);

        if (! $offre) {
            return null;
        }

        $offre['besoin'] = (new BesoinsModel())->getBesoinAvecDetails((int) $offre['besoin_id']);

        return $offre;
    }
}

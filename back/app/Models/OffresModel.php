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
            'dp.salaire_min as salaire_propose,dp.mode_de_travail as mode_de_travail,' .
            'dp.competences_techniques as competences_techniques,dp.formation as formation, u.email AS rh_email,'.
            'Offre.besoin_id,p.libelle as poste_libelle,Offre.id_offre'
        )   
            ->join('details_besoins db' , 'db.besoins_id = Offre.besoin_id')
            ->join('details_poste dp','dp.poste_id = db.poste_id')
            ->join('postes p','p.id = dp.poste_id')
            ->join('statut_offre so', 'so.id_statut_offre = Offre.statut_offre_id')
            ->join('utilisateurs u', 'u.id = Offre.id_rh')
            ->find($offreId);

        if (! $offre) {
            return null;
        }

        // $offre['besoin'] = (new BesoinsModel())->getBesoinAvecDetails((int) $offre['besoin_id']);

        return $offre;
    }

}

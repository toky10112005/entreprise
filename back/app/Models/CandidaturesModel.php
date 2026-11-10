<?php

namespace App\Models;

use CodeIgniter\Model;

class CandidaturesModel extends Model
{
    protected $table            = 'candidatures';
    protected $primaryKey       = 'id_candidature';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'offre_id',
        'candidat_id',
        'cv',
        'lettre_motivation',
        'statut_candidature_id',
        'date_insertion',
        'date_modification',
        'commentaire'
    ];

    protected $validationRules = [
        'offre_id'              => 'required|is_natural_no_zero|is_not_unique[Offre.id_offre]',
        'candidat_id'           => 'required|is_natural_no_zero|is_not_unique[candidats.id_candidat]',
        'cv'                    => 'required|string|max_length[255]',
        'statut_candidature_id' => 'required|is_natural_no_zero|is_not_unique[statut_candidature.id_statut_candidature]',
    ];

    /**
     * Récupère les détails complets d'une candidature (Candidat + Offre + Statuts).
     */
    public function getCandidatureComplete(int $idCandidature): ?array
    {
        return $this->select('candidatures.*, 
                              c.nom AS candidat_nom, c.prenom AS candidat_prenom, c.email AS candidat_email,
                              o.id_offre, sc.libelle AS statut_candidature_libelle')
            ->join('candidats c', 'c.id_candidat = candidatures.candidat_id')
            ->join('Offre o', 'o.id_offre = candidatures.offre_id')
            ->join('statut_candidature sc', 'sc.id_statut_candidature = candidatures.statut_candidature_id')
            ->where('candidatures.id_candidature', $idCandidature)
            ->first();
    }

    /**
     * Liste toutes les candidatures associées à une offre spécifique.
     */
    public function getCandidaturesParOffre(int $offreId): array
    {
        return $this->select('candidatures.*, c.nom, c.prenom, c.email, sc.libelle AS statut_candidature')
            ->join('candidats c', 'c.id_candidat = candidatures.candidat_id')
            ->join('statut_candidature sc', 'sc.id_statut_candidature = candidatures.statut_candidature_id')
            ->where('candidatures.offre_id', $offreId)
            ->orderBy('candidatures.date_insertion', 'DESC')
            ->findAll();
    }
}
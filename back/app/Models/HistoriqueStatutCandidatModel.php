<?php

namespace App\Models;

use CodeIgniter\Model;

class HistoriqueStatutCandidatModel extends Model
{
    protected $table            = 'HistoriqueStatutCandidat';
    protected $primaryKey       = 'id_historique_statut_candidat';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'candidat_id',
        'statut_candidat_id',
        'date_insertion',
        'commentaire'
    ];

    protected $validationRules = [
        'candidat_id'        => 'required|is_natural_no_zero|is_not_unique[candidats.id_candidat]',
        'statut_candidat_id' => 'required|is_natural_no_zero|is_not_unique[statut_candidat.id_statut_candidat]',
        'date_insertion'     => 'valid_date[Y-m-d H:i:s]',
    ];

    /**
     * Insère une ligne d'historique pour un candidat.
     */
    public function insertHistorique(int $candidatId, int $statutCandidatId, ?string $commentaire = null): bool
    {
        return (bool) $this->insert([
            'candidat_id'        => $candidatId,
            'statut_candidat_id' => $statutCandidatId,
            'date_insertion'     => date('Y-m-d H:i:s'),
            'commentaire'        => $commentaire,
        ]);
    }

    /**
     * Récupère l'historique complet d'un candidat.
     */
    public function getHistoriqueParCandidat(int $candidatId): array
    {
        return $this->select('HistoriqueStatutCandidat.*, sc.libelle AS statut_libelle')
            ->join('statut_candidat sc', 'sc.id_statut_candidat = HistoriqueStatutCandidat.statut_candidat_id')
            ->where('candidat_id', $candidatId)
            ->orderBy('date_insertion', 'DESC')
            ->findAll();
    }
}
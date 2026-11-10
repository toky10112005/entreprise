<?php

namespace App\Models;

class HistoriqueCandidatureModel extends \CodeIgniter\Model
{
    protected $table            = 'historique_candidature';
    protected $primaryKey       = 'id_historique_candidature';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'candidature_id',
        'statut_candidature_id',
        'date_insertion',
        'commentaire'
    ];

    protected $validationRules = [
        'candidature_id'        => 'required|is_natural_no_zero|is_not_unique[candidatures.id_candidature]',
        'statut_candidature_id' => 'required|is_natural_no_zero|is_not_unique[statut_candidature.id_statut_candidature]',
        'date_insertion'        => 'required|valid_date[Y-m-d H:i:s]',
    ];

    /**
     * Enregistre un historique de statut pour une candidature donnée.
     */
    public function ajouterHistorique(int $candidatureId, int $statutCandidatureId, ?string $commentaire = null): bool
    {
        return (bool) $this->insert([
            'candidature_id'        => $candidatureId,
            'statut_candidature_id' => $statutCandidatureId,
            'date_insertion'        => date('Y-m-d H:i:s'),
            'commentaire'           => $commentaire,
        ]);
    }

    /**
     * Récupère l'historique complet des statuts d'une candidature avec les libellés.
     */
    public function getHistoriqueParCandidature(int $candidatureId): array
    {
        return $this->select('historique_candidature.*, sc.libelle AS statut_candidature_libelle')
            ->join('statut_candidature sc', 'sc.id_statut_candidature = historique_candidature.statut_candidature_id')
            ->where('candidature_id', $candidatureId)
            ->orderBy('date_insertion', 'DESC')
            ->findAll();
    }
}
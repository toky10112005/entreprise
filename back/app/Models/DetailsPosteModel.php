<?php

namespace App\Models;

use CodeIgniter\Model;

class DetailsPosteModel extends Model
{
    protected $table            = 'details_poste';
    protected $primaryKey       = 'id_detail_poste';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'poste_id',
        'salaire_min',
        'salaire_max',
        'objectifs',
        'valeur',
        'mode_de_travail',
        'competences_techniques',
        'formation'
    ];

    // Dates
    protected $useTimestamps = false;

    // Règles de validation CI4
    protected $validationRules = [
        'poste_id'               => 'required|is_natural_no_zero|is_not_unique[postes.id]',
        'salaire_min'            => 'permit_empty|numeric|greater_than_equal_to[0]',
        'salaire_max'            => 'permit_empty|numeric|greater_than_equal_to[0]',
        'mode_de_travail'        => 'permit_empty|string|max_length[50]',
        'valeur'                 => 'permit_empty|string|max_length[100]',
        'objectifs'              => 'permit_empty|string',
        'competences_techniques' => 'permit_empty|string',
        'formation'              => 'permit_empty|string',
    ];

    protected $validationMessages = [
        'poste_id' => [
            'required'           => 'L\'identifiant du poste est requis.',
            'is_natural_no_zero' => 'L\'ID du poste doit être un entier valide.',
            'is_not_unique'      => 'Le poste spécifié n\'existe pas dans la base de données.',
        ],
        'salaire_min' => [
            'numeric'               => 'Le salaire minimum doit être un montant numérique.',
            'greater_than_equal_to' => 'Le salaire minimum ne peut pas être négatif.',
        ],
        'salaire_max' => [
            'numeric'               => 'Le salaire maximum doit être un montant numérique.',
            'greater_than_equal_to' => 'Le salaire maximum ne peut pas être négatif.',
        ],
    ];

    /**
     * Récupère le détail d'un poste avec les informations du poste et de son département.
     */
    public function getDetailsParPoste(int $posteId): ?array
    {
        return $this->select('details_poste.*, p.libelle AS poste_libelle, d.id_departement, d.libelle AS departement_libelle')
            ->join('postes p', 'p.id = details_poste.poste_id')
            ->join('departement d', 'd.id_departement = p.departement_id', 'left')
            ->where('details_poste.poste_id', $posteId)
            ->first();
    }

    /**
     * Insère ou met à jour les détails d'un poste (Upsert) en vérifiant la cohérence salariale.
     *
     * @throws \InvalidArgumentException Si le salaire min est supérieur au salaire max.
     */
    public function enregistrerDetails(array $data): bool
    {
        // Validation métier : salaire_min <= salaire_max
        if (!empty($data['salaire_min']) && !empty($data['salaire_max'])) {
            if ((float)$data['salaire_min'] > (float)$data['salaire_max']) {
                throw new \InvalidArgumentException('Le salaire minimum ne peut pas être supérieur au salaire maximum.');
            }
        }

        // Vérification si un enregistrement existe déjà pour ce poste_id
        $existant = $this->where('poste_id', $data['poste_id'])->first();

        if ($existant) {
            return $this->update($existant['id_detail_poste'], $data);
        }

        return (bool) $this->insert($data);
    }
}
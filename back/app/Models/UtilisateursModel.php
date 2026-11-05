<?php

namespace App\Models;

use CodeIgniter\Model;

class UtilisateursModel extends Model
{
    protected $table         = 'utilisateurs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'role_id', 'nom', 'prenom', 'email', 'mot_de_passe',
        'departement_id', 'telephone', 'actif',
    ];

    // Le mot de passe est haché automatiquement à l'insertion et à la modification.
    protected $beforeInsert = ['hashMotDePasse'];
    protected $beforeUpdate = ['hashMotDePasse'];

    protected $validationRules = [
        'role_id'        => 'required|is_natural_no_zero|is_not_unique[roles.id]',
        'nom'            => 'required|string|max_length[100]',
        'prenom'         => 'required|string|max_length[100]',
        'email'          => 'required|valid_email|max_length[255]|is_unique[utilisateurs.email]',
        'mot_de_passe'   => 'required|string|min_length[6]|max_length[72]',
        // Vide autorisé : RH, finance et direction n'ont pas de département.
        'departement_id' => 'permit_empty|is_natural_no_zero|is_not_unique[departements.id]',
        'telephone'      => 'permit_empty|string|max_length[30]',
        'actif'          => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'role_id' => [
            'required'           => 'Le rôle est requis.',
            'is_natural_no_zero' => 'Le rôle doit être un identifiant valide.',
            'is_not_unique'      => 'Ce rôle n\'existe pas.',
        ],
        'nom' => [
            'required'   => 'Le nom est requis.',
            'string'     => 'Le nom doit être une chaîne de caractères.',
            'max_length' => 'Le nom ne peut pas dépasser 100 caractères.',
        ],
        'prenom' => [
            'required'   => 'Le prénom est requis.',
            'string'     => 'Le prénom doit être une chaîne de caractères.',
            'max_length' => 'Le prénom ne peut pas dépasser 100 caractères.',
        ],
        'email' => [
            'required'    => 'L\'email est requis.',
            'valid_email' => 'L\'email doit être une adresse email valide.',
            'max_length'  => 'L\'email ne peut pas dépasser 255 caractères.',
            'is_unique'   => 'Cet email est déjà utilisé.',
        ],
        'mot_de_passe' => [
            'required'   => 'Le mot de passe est requis.',
            'string'     => 'Le mot de passe doit être une chaîne de caractères.',
            'min_length' => 'Le mot de passe doit contenir au moins 6 caractères.',
            'max_length' => 'Le mot de passe ne peut pas dépasser 72 caractères.',
        ],
        'departement_id' => [
            'is_natural_no_zero' => 'Le département doit être un identifiant valide.',
            'is_not_unique'      => 'Ce département n\'existe pas.',
        ],
        'telephone' => [
            'max_length' => 'Le téléphone ne peut pas dépasser 30 caractères.',
        ],
        'actif' => [
            'in_list' => 'La valeur de "actif" doit être 0 ou 1.',
        ],
    ];

    /**
     * Callback : hache le mot de passe s'il n'est pas déjà haché.
     */
    protected function hashMotDePasse(array $donnees): array
    {
        if (! isset($donnees['data']['mot_de_passe'])) {
            return $donnees;
        }

        $motDePasse = $donnees['data']['mot_de_passe'];

        if (empty(password_get_info($motDePasse)['algo'])) {
            $donnees['data']['mot_de_passe'] = password_hash($motDePasse, PASSWORD_DEFAULT);
        }

        return $donnees;
    }

    public function getUtilisateurByEmail($email): ?array
    {
        return $this->where('email', $email)->first();
    }

    public function getUtilisateurById($id): ?array
    {
        return $this->find($id);
    }

    /**
     * Modification d'un utilisateur : l'unicité de l'email ignore la ligne modifiée.
     * (Avec update() seul, l'email de l'utilisateur lui-même serait vu comme un doublon.)
     */
    public function modifierUtilisateur(int $id, array $donnees): bool
    {
        $this->setValidationRule(
            'email',
            'required|valid_email|max_length[255]|is_unique[utilisateurs.email,id,' . $id . ']'
        );

        return $this->update($id, $donnees);
    }

    /**
     * Connexion : retourne l'utilisateur (sans mot de passe, avec le libellé du rôle)
     * ou null si l'email, le mot de passe ou l'état "actif" ne conviennent pas.
     */
    public function verifierConnexion(string $email, string $motDePasse): ?array
    {
        $utilisateur = $this->select('utilisateurs.*, r.libelle AS role_libelle')
            ->join('roles r', 'r.id = utilisateurs.role_id', 'left')
            ->where('utilisateurs.email', $email)
            ->first();

        if (! $utilisateur || (int) $utilisateur['actif'] !== 1) {
            return null;
        }

        if (! password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
            return null;
        }

        unset($utilisateur['mot_de_passe']);

        return $utilisateur;
    }
}
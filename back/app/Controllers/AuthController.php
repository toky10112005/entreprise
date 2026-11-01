<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UtilisateurModel;

class AuthController extends BaseController
{
    public function login()
    {
        $json = $this->request->getJSON(true);

        if (!$json || empty($json['email']) || empty($json['mot_de_passe'])) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Email et mot de passe requis']);
        }

        $model = new UtilisateurModel();
        $user = $model->getUserByEmail(trim($json['email']));

        if (!$user || !password_verify($json['mot_de_passe'], $user['mot_de_passe'])) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['error' => 'Identifiants incorrects']);
        }

        $userData = [
            'id' => $user['id'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'email' => $user['email'],
            'role' => $user['role'],
            'departement_id' => $user['departement_id'],
            'telephone' => $user['telephone'] ?? null,
        ];

        $token = base64_encode(random_bytes(32));

        return $this->response->setJSON([
            'message' => 'Connexion réussie',
            'user' => $userData,
            'token' => $token,
        ]);
    }

    public function attempt()
    {
        return $this->login();
    }

    public function logout()
    {
        return $this->response->setJSON([
            'message' => 'Déconnexion réussie',
            'success' => true,
        ]);
    }

    public function register()
    {
        $json = $this->request->getJSON(true);

        if (!$json) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Données invalides']);
        }

        $required = ['nom', 'prenom', 'email', 'mot_de_passe'];
        foreach ($required as $field) {
            if (empty($json[$field])) {
                return $this->response
                    ->setStatusCode(400)
                    ->setJSON(['error' => "Le champ {$field} est requis"]);
            }
        }

        $model = new UtilisateurModel();

        if ($model->where('email', trim($json['email']))->first()) {
            return $this->response
                ->setStatusCode(409)
                ->setJSON(['error' => 'Un compte avec cet email existe déjà']);
        }

               $payload = [
            'nom' => trim($json['nom']),
            'prenom' => trim($json['prenom']),
            'email' => trim($json['email']),
            
            'mot_de_passe' => password_hash($json['mot_de_passe'], PASSWORD_DEFAULT),
            'role' => 'candidat',
            
            'departement_id' => null, 
            'telephone' => !empty($json['telephone']) ? trim($json['telephone']) : null,
            'actif' => 1,
        ];

        $id = $model->insert($payload);

        if (!$id) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['error' => 'Impossible de créer le compte',
                 'details' => $model->errors(),
                 'db_error' => $model->db->error()
                 ]);
        }

        return $this->response->setJSON([
            'message' => 'Compte candidat créé avec succès',
            'user' => [
                'id' => $id,
                'nom' => $payload['nom'],
                'prenom' => $payload['prenom'],
                'email' => $payload['email'],
                'role' => 'candidat',
                'departement_id' => $payload['departement_id'],
                'telephone' => $payload['telephone'],
            ],
        ]);
    }
}
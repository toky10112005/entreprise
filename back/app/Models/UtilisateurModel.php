<?php
namespace App\Models;
use CodeIgniter\Model;

class UtilisateurModel extends Model
{
    protected $table            = 'utilisateurs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    protected $allowedFields    = ['nom', 'prenom', 'email', 'mot_de_passe', 'role', 'departement_id', 'actif', 'telephone']; 

    public function getUserByEmail($email)
    {
        return $this->where('email', $email)->where('actif', 1)->first();
    }
}
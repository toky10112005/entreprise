<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $header = $request->getHeaderLine('Authorization');

        if (!preg_match('/Bearer\s(\S+)/', $header, $m)) {
            return service('response')->setStatusCode(401)
                ->setJSON(['error' => 'Token manquant']);
        }

        try {
            $decoded = JWT::decode($m[1], new Key(env('JWT_SECRET'), 'HS256'));
        } catch (\Throwable $e) {
            return service('response')->setStatusCode(401)
                ->setJSON(['error' => 'Token invalide ou expiré']);
        }

        // Si des rôles sont précisés (role:Manager,Admin), on les vérifie
        if (!empty($arguments) && !in_array($decoded->role, $arguments, true)) {
            return service('response')->setStatusCode(403)
                ->setJSON(['error' => 'Accès refusé']);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
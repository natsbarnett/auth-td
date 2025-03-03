<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHandler
{
    private $privateKey;
    private $publicKey;
    private $publicRefresh;
    private $privateRefresh;

    public function __construct($privateKeyPath, $publicKeyPath)
    {
        // Charger les clés depuis les fichiers
        $this->privateKey = file_get_contents($privateKeyPath);
        $this->publicKey = file_get_contents($publicKeyPath);
        $this->privateRefresh = file_get_contents('./private_refresh.pem');
        $this->publicRefresh = file_get_contents('./public_refresh.pem');
    }

    /**
     * Générer un JWT signé avec RSA (RS256)
     */
    public function generateJwt($payload)
    {
        return JWT::encode($payload, $this->privateKey, 'RS256');
    }
/* 
    public function generateRefresh($username)
    {
        $payload = [
            'username' => $username,
            'exp' => time() + 60 * 60 * 24 * 30,
        ];
        return JWT::encode($payload, $this->privateRefresh, 'RS256');
    } */

    /**
     * Vérifier et décoder un JWT
     */
    public function verifyJwt($jwt)
    {
        try {
            return JWT::decode($jwt, new Key($this->publicKey, 'RS256'));
        } catch (Exception $e) {
            return false;
        }
    }

  

}
?>
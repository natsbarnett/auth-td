<?php
require 'vendor/autoload.php';
require_once 'JwtHandler.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class JwtManager
{
    private $jwtHandler;
    private $refreshSecretKey;
    private $tokenStorageFile = 'tokens.json'; // Fichier pour stocker les refresh tokens

    public function __construct($jwtHandler, $refreshSecretKey)
    {
        $this->jwtHandler = $jwtHandler;
        $this->refreshSecretKey = file_get_contents($refreshSecretKey);
    }

    // Générer un refresh token et l'enregistrer dans le fichier JSON
    public function generateRefreshToken($userId)
    {
        $expiresAt = time() + 60 * 60 * 24 * 30; // Expiration dans 30 jours

        $payload = [
            'userId' => $userId,
            'exp' => $expiresAt
        ];

        $refreshToken_unhash = JWT::encode($payload, $this->refreshSecretKey, 'RS256');
        $refreshToken = password_hash($refreshToken_unhash, PASSWORD_BCRYPT);

        // Stocker le token avec sa date d'expiration
        $this->storeRefreshToken($userId, $refreshToken, $expiresAt);
        $this->storeRefreshToken($userId, $refreshToken_unhash, $expiresAt);
        setcookie('refresh_jwt' , $refreshToken_unhash);

        return $refreshToken;
    }


    // Stocker le refresh token dans un fichier JSON
    private function storeRefreshToken($username, $refreshToken, $expiresAt)
    {
        $tokens = file_exists($this->tokenStorageFile) ? json_decode(file_get_contents($this->tokenStorageFile), true) : [];

        if (!isset($tokens[$username])) {
            $tokens[$username] = [];
        }

        // Hacher le refresh token avant de le stocker
        $hashedToken = password_hash($refreshToken, PASSWORD_BCRYPT);

        $tokens[$username][] = [
            'token' => $hashedToken,
            'expires_at' => $expiresAt
        ];

        file_put_contents($this->tokenStorageFile, json_encode($tokens, JSON_PRETTY_PRINT));
    }

    // Vérifier et rafraîchir l'access token
    public function refreshAccessToken($refreshToken)
    {
        try {
            $publicKey = file_get_contents('./public_refresh.pem');
            $decoded = JWT::decode($refreshToken, new Key($publicKey, 'RS256'));
            $username = $decoded->userId;

            $tokens = file_exists($this->tokenStorageFile) ? json_decode(file_get_contents($this->tokenStorageFile), true) : [];

            if (!isset($tokens[$username])) {
                return json_encode(['error' => 'Invalid refresh token']);
            }

            // Vérifier si le token existe et n'a pas expiré
            foreach ($tokens[$username] as $key => $storedToken) {
                if (password_verify($refreshToken, $storedToken['token'])) {
                    if ($storedToken['expires_at'] < time()) {
                        return json_encode(['error' => 'Refresh token expired']);
                    }

                    // Générer un nouvel access token
                    $newAccessToken = $this->jwtHandler->generateJwt(['userId' => $username, 'exp' => time() + 3600]);

                    return json_encode([
                        'accessToken' => $newAccessToken
                    ]);
                }
            }

            return json_encode(['error' => 'Invalid refresh token']);
        } catch (Exception $e) {
            return json_encode(['error' => 'Invalid refresh token']);
        }
    }



    public function revokeRefreshToken($username, $refreshToken = null)
    {
        $tokens = file_exists($this->tokenStorageFile) ? json_decode(file_get_contents($this->tokenStorageFile), true) : [];

        if (!isset($tokens[$username])) {
            return;
        }

        // Filtrer les tokens : supprimer celui demandé + ceux qui sont expirés
        $tokens[$username] = array_filter($tokens[$username], function ($t) use ($refreshToken) {
            return ($refreshToken === null || !password_verify($refreshToken, $t['token'])) && $t['expires_at'] > time();
        });

        // Si plus aucun token n'existe pour cet utilisateur, on le supprime
        if (empty($tokens[$username])) {
            unset($tokens[$username]);
        }

        file_put_contents($this->tokenStorageFile, json_encode($tokens, JSON_PRETTY_PRINT));
    }





}

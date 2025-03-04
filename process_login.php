<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$username = 'admin';
$password = 'mmi';
$users = [
    "admin" => "mmi"
];

$secret_key = 'votre_clé_secrète';
require('Login.class.php');
require('CSRFHandler.php');
require_once 'JwtHandler.php';
require_once 'JwtManager.php';

header('Content-Type: application/json');
$jsonData = json_decode(file_get_contents("php://input"));

$csrfToken = $jsonData->csrfToken; // Récupérer le token envoyé par le formulaire
$jwtHandler = new JwtHandler(__DIR__ . '/private.pem', __DIR__ . './public.pem');
$jwtManager = new JwtManager($jwtHandler, __DIR__ . '/private_refresh.pem');


$login = new Login($users, $secret_key, $csrfToken, $jwtHandler);

if (!CSRF::verifyCSRF($csrfToken)) {
    die("Erreur CSRF : token invalide.");
}

$token = $login->authenticate($username, $password, $csrfToken);

// Traitement de la requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $data = array_combine(
        array_map('trim', array_keys($data)),
        array_map('trim', $data)
    );

    $error_message = '';
    if (isset($data["username"]) && isset($data["password"])) {
        $username = $data["username"];
        $password = $data["password"];

        $jwt = $login->authenticate($username, $password, $csrfToken);
        if (!$jwt) {
            $error_message = 'Invalid username or password';
        }
    } else {
        $error_message = 'Username and password required';
    }

    // Si l'authentification a réussi, on génère également un refresh token
    if ($error_message === '') {
        // Générer le refresh token et le retourner avec l'access token
        $refreshToken = $jwtManager->generateRefreshToken($username);
        
        // Définir les cookies avec les tokens
        setcookie('jwt', $jwt, time() + 3600, "/");
        setcookie('refresh_token', $refreshToken, time() + 60 * 60 * 24 * 30, "/"); // Expiration de 30 jours
        
        // Réponse avec redirection dans le JSON
        ob_clean();
        echo json_encode([
            'jwt' => $jwt,
            'refreshToken' => $refreshToken,
            'csrf' => $csrfToken,
            'redirect' => "/hello/" // Ajoutez ici l'URL vers laquelle vous voulez rediriger
        ]);
    } else {
        ob_clean(); // Nettoie le buffer de sortie avant d'envoyer la réponse JSON
        echo json_encode([
            'error' => $error_message,
        ]);
    }

    

    exit();

}
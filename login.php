<?php
require('Login.class.php');
require('CSRFHandler.php');
header('Content-Type: application/json');

// Utilisateur et mot de passe en dur (en dev seulement)
$users = [
    'admin' => 'mmi',
];

// Clé secrète pour signer le JWT
$secret_key = "votre_clé_secrète";  // À ne pas partager en production

// Créer une instance de la classe Login
$login = new Login($users, $secret_key);

// Vérification des données envoyées
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['username']) || !isset($data['password'])) {
    echo json_encode(['error' => 'Username and password required']);
    exit();
}

$csrf = new CSRF();
$username = $data['username'];
$password = $data['password'];
$csrf = $csrf->createCSRF();
echo json_encode(['csrf' => $csrf]);
// Vérification du login et génération du jeton
$jwt = $login->authenticate($username, $password, $csrf);

if ($jwt) {
    echo json_encode(['jwt' => $jwt]);
    $login->cookie($jwt, time() + 60 * 60 * 24 * 30);
} else {
    echo json_encode(['error' => 'Invalid username or password']);
}

exit();

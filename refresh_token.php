<?php
session_start();
require 'vendor/autoload.php';
require_once 'JwtHandler.php';
require_once 'JwtManager.php';

header('Content-Type: application/json');

$jsonData = json_decode(file_get_contents("php://input"));
$refreshToken = $jsonData->refreshToken ?? '';

// Jwt->verify($user, $refreshToken)

$jwtHandler = new JwtHandler('./private.pem', './public.pem');
$jwtManager = new JwtManager($jwtHandler, './private_refresh.pem');

$response = $jwtManager->refreshAccessToken($refreshToken);
echo $response;
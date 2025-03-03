<?php
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Définition des variables de JWT
 */

if (isset($_COOKIE["refresh_jwt"]) && isset($_COOKIE["refresh_token"]) && isset($_COOKIE["jwt"])) {
    if (password_verify($_COOKIE["refresh_jwt"], $_COOKIE["refresh_token"])) {
        $jwt = $_COOKIE["jwt"];
        $jwt_refresh = $_COOKIE["refresh_token"];
        $jwt_unhash = $_COOKIE["refresh_jwt"];

        $cle_prive_jwt = file_get_contents('../private.pem');
        $cle_public_jwt = file_get_contents('../public.pem');


        //var_dump($jwt);
        //$jwt = json_decode($jwt);
        $decoded = JWT::decode($jwt, new Key($cle_public_jwt, 'RS256'));
        //var_dump($decoded);


        $rss = simplexml_load_file('./data/data.xml');
        echo '<h1>Bienvenue ' . $decoded->username . ' dans le ' . $rss->channel->title . '</h1>';

        foreach ($rss->channel->item as $item) {
            echo '<h2>' . $item->title . '</h2>';
            echo $item->description;
        } 

    } else {
        exit();
    }
} else {
    exit();
}


<?php
require '../vendor/autoload.php';


if (!isset($_COOKIE["refresh_jwt"]) || !isset($_COOKIE["refresh_token"]) || !isset($_COOKIE["jwt"])) {
    exit();
}
else if (!password_verify($_COOKIE["refresh_jwt"], $_COOKIE["refresh_token"])){
    exit();
}
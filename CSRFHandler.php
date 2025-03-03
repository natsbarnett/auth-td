<?php


class CSRF
{
    //private $token;

    /*public function __construct(){
        $this->token = bin2hex(random_bytes(32));
    }*/



    // Méthode pour récupérer le token CSRF
    public static function getToken()
    {
        return $_SESSION['csrf_token'];  // Retourne une chaîne de caractères
    }

    public static function generateCSRF()
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    public static function verifyCSRF($token)
    {
        return isset($_SESSION['csrf_token']) && ($_SESSION['csrf_token'] == $token);
    }
}
?>
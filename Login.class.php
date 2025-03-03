<?php

class Login
{
    private $users;
    private $secret_key;
    private $csrf;
    private $jwtHandler;

    public function __construct($users, $secret_key, $csrf, $jwtHandler)
    {
        $this->users = $users;
        $this->secret_key = $secret_key;
        $this->csrf = $csrf;
        $this->jwtHandler = $jwtHandler;
    }

    public function authenticate($username, $password, $csrfToken)
    {
        if (!isset($this->users[$username]) || $this->users[$username] !== $password) {
            return false;
        }

        if (!CSRF::verifyCSRF($csrfToken)) {
            return false;
        }

        $payload = [
            'username' => $username,
            'exp' => time() + 3600
        ];
       

        return $this->jwtHandler->generateJwt($payload);
    }
    
}



?>
<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require('CSRFHandler.php');

$csrf_token = CSRF::generateCSRF();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
</head>

<body>
    <h2>Login</h2>
    <form id="loginForm">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required /><br /><br />

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required /><br /><br />

        <input type="hidden" name="csrfToken" value="<?php echo $csrf_token; ?>" id="csrf_token">


        <button type="submit">Login</button>
    </form>

    <div id="message"></div>

    <script>
        document.getElementById("loginForm").addEventListener("submit", async function (event) {
            event.preventDefault();

            const username = document.getElementById("username").value;
            const password = document.getElementById("password").value;
            const csrfToken = document.getElementById("csrf_token").value;

            const requestData = { username, password, csrfToken };
            const response = await fetch("process_login.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(requestData),
            });

            const text = await response.text();  // Récupérer la réponse brute pour debug

            try {
                const data = JSON.parse(text); // Tenter de parser en JSON

                if (data.redirect) {
                    window.location.href = data.redirect;  // Redirige si un lien est fourni
                } else {
                    document.getElementById("message").innerHTML = `<p style="color: red;">Login failed: ${data.error}</p>`;
                }
            } catch (error) {
                console.error("Erreur de parsing JSON :", error);
                document.getElementById("message").innerHTML = `<p style="color: red;">Erreur interne.</p>`;
            }
        });


        async function refreshToken() {
            const refreshToken = getCookie("refresh_token"); // Récupérer le refresh token du cookie

            if (!refreshToken) {
                console.error("No refresh token found");
                return;
            }

            const response = await fetch("refresh_token.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ refreshToken }),
            });

            const data = await response.json();
            if (data.accessToken) {
                document.cookie = `jwt=${data.accessToken}; path=/;`;
            } else {
                console.error("Error refreshing token:", data.error);
            }
        }

        // Fonction pour récupérer un cookie
        function getCookie(name) {
            const match = document.cookie.match(new RegExp("(^| )" + name + "=([^;]+)"));
            return match ? match[2] : null;
        }


    </script>
</body>

</html>
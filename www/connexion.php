<?php
require_once "../bdd/connexion_bdd.php";
?>

<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error_message = "Veuillez remplir tous les champs.";
    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Adresse email au format invalide.";
    }

    elseif (strlen($email) > 100) {
        $error_message = "L'adresse email dépasse la longueur maximale autorisée.";
    }

    else {
        $stmt = $bdd->prepare("SELECT * FROM utilisateurs WHERE adresse_mail = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['user'] = $user;
            $_SESSION['email'] = $user['adresse_mail'];

            header("Location: index.php");
            exit;
        } else {
            $error_message = "Adresse email ou mot de passe incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <title>Connexion</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    </head>
    <body>
        <div id="navBar"></div>
        <div class="container">
            <div id="controlPanel" class="controlPanel"></div>
            <main>
                <h1>Connexion</h1>

                <div id="form-error">
                    <span class="icon">⚠️</span>
                    <span id="error-message"></span>
                </div>

                <form action="#" method="POST" class="form-container">
                    <label for="email">Adresse Email :</label>
                    <input type="email" id="email" name="email">

                    <label for="password">Mot de passe :</label>
                    <input type="password" id="password" name="password">

                    <p class="mdpOublie">
                        <a href="#">Mot de passe oublié ?</a>
                    <p>

                    <button type="submit">Se connecter</button>
                </form>
                <p>Vous avez déjà un compte ? <a href="inscription.php">Inscrivez-vous</a></p>
                <script>
                    function showError(message) {
                        const box = document.getElementById("form-error");
                        const msg = document.getElementById("error-message");

                        msg.textContent = message;
                        box.style.display = "flex";

                        setTimeout(() => {
                            box.style.display = "none";
                        }, 4000);
                    }
                </script>
                <?php if (!empty($error_message)) : ?>
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            showError("<?= $error_message ?>");
                        });
                    </script>
                <?php endif; ?>
            </main>
        </div>
        <script>
            $(function() {
                $("#navBar").load("navBar.php");
                $("#controlPanel").load("controlPanel.php");
            });
        </script>
    </body>
</html>
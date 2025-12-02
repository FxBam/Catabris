<?php
require_once "../bdd/connexion_bdd.php";
session_start();

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = strtolower(trim($_POST['nom']));
    $prenom = strtolower(trim($_POST['prenom']));
    $adresse = strtolower(trim($_POST['adresse']));
    $code_postal = trim($_POST['code-postal']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm-password']);

    if (
        empty($nom) || empty($prenom) || empty($adresse) ||
        empty($code_postal) || empty($email) ||
        empty($password) || empty($confirm_password)
    ) {
        $error_message = "Veuillez remplir tous les champs.";
    }

    elseif (strlen($nom) > 50 || strlen($prenom) > 50 || strlen($adresse) > 200 || strlen($email) > 100) {
        $error_message = "Un des champs dépasse la longueur maximale autorisée.";
    }

    elseif (ctype_digit($nom) || ctype_digit($prenom)) {
        $error_message = "Le nom et le prénom ne doivent pas contenir de chiffres.";
    }

    elseif (!ctype_digit($code_postal) || strlen($code_postal) != 5) {
        $error_message = "Code postal invalide.";
    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Adresse email invalide.";
    }

    else {
        $stmt = $bdd->prepare("SELECT COUNT(*) FROM utilisateurs WHERE adresse_mail = ?");
        $stmt->execute([$email]);

        if ($stmt->fetchColumn() > 0) {
            $error_message = "Cette adresse email est déjà utilisée.";
        }

        elseif ($password !== $confirm_password) {
            $error_message = "Les mots de passe ne correspondent pas.";
        }

        else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $bdd->prepare("INSERT INTO utilisateurs (nom, prenom, adresse, code_postal, adresse_mail, mot_de_passe)
            VALUES (?, ?, ?, ?, ?, ?)");

            if ($stmt->execute([$nom, $prenom, $adresse, $code_postal, $email, $hashed])) {
                header('Location: connexion.php');
                exit;
            } else {
                $error_message = "Erreur lors de l'inscription.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='0.9em' font-size='90'>🏟️</text></svg>">
        <link rel="stylesheet" href="style/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <title>Inscription</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    </head>
    <body>
        <div id="navBar"></div>
        <div class="container">
            <div id="controlPanel" class="controlPanel"></div>
            <main>
                <h1>Inscription</h1>

                <div id="form-error">
                    <span class="icon">⚠️</span>
                    <span id="error-message"></span>
                </div>

                <form action="#" method="POST" class="form-container">
                    <label>Nom :</label>
                    <input type="text" name="nom">

                    <label>Prénom :</label>
                    <input type="text" name="prenom">

                    <label>Adresse :</label>
                    <input type="text" name="adresse">

                    <label>Code postal :</label>
                    <input type="text" name="code-postal">

                    <label>Email :</label>
                    <input type="email" name="email">

                    <label>Mot de passe :</label>
                    <input type="password" name="password">

                    <label>Confirmer le mot de passe :</label>
                    <input type="password" name="confirm-password">

                    <button type="submit">Créer mon compte</button>
                </form>
                <p>Vous avez déjà un compte ? <a href="connexion.php">Connectez-vous</a></p>

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
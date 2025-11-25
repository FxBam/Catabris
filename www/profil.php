<?php
require_once "../bdd/connexion_bdd.php";
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];

$error_message = '';

if (isset($_POST['supprimer'])) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error_message = "Veuillez remplir tous les champs.";
    } elseif ($email !== $user['adresse_mail']) {
        $error_message = "Email incorrect.";
    } elseif (!password_verify($password, $user['mot_de_passe'])) {
        $error_message = "Mot de passe incorrect.";
    }

    if (empty($error_message)) {
        $stmt = $bdd->prepare("DELETE FROM utilisateurs WHERE adresse_mail = :email");
        $stmt->bindParam(':email', $user['adresse_mail']);
        $stmt->execute();
        session_destroy();
        header("Location: index.php");
        exit;
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
        <title>Profil</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    </head>
    <body>
        <div id="navBar"></div>
        <div class="container">
            <div id="controlPanel" class="controlPanel"></div>
            <main>
                <h1>Bienvenue <?= htmlspecialchars($user['prenom'] . " " . $user['nom']); ?></h1>
                <h2>Supprimer mon compte</h2>

                <div id="form-error">
                    <span class="icon">⚠️</span>
                    <span id="error-message"></span>
                </div>

                <form method="post" class="form-container">
                    <label>Email : <input type="email" name="email"></label>
                    <label>Mot de passe : <input type="password" name="password"></label>
                    <button type="submit" name="supprimer" class="btn-delete">Supprimer mon compte</button>
                </form>

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

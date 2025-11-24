<?php
require_once "../bdd/connexion_bdd.php";
session_start();
?>

<?php
if (!isset($_SESSION['user'])) {
    header("Location: connexion.php");
    exit;
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <title>Déconnexion</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    </head>
    <body>
        <div id="navBar"></div>
        <div class="container">
            <div id="controlPanel" class="controlPanel"></div>
            <main>
                <h1>Déconnexion</h1>

                <div class="form-container">
                    <p style="font-size:1.2em;">
                        Bonjour <strong><?= htmlspecialchars($user['prenom']) ?> <?= htmlspecialchars($user['nom']) ?></strong> 👋
                    </p>

                    <p>Vous voulez vraiment nous quitter ?</p>

                    <div class="logout-buttons">
                        <form action="logout.php" method="post" style="display:inline;">
                            <button type="submit">Se déconnecter</button>
                        </form>
                        <a href="index.php" class="cp-btn">Annuler</a>
                    </div>
                </div>
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
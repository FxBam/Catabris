<?php
require_once "../bdd/connexion_bdd.php";
session_start();

if (!isset($_SESSION['user']) || !$_SESSION['user']['compte_admin']) {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['email'])) {
    header("Location: dashboard.php");
    exit;
}

$email = $_GET['email'];

$stmt = $bdd->prepare("SELECT * FROM utilisateurs WHERE adresse_mail = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Utilisateur introuvable.";
    exit;
}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $adresse = $_POST['adresse'];
    $code_postal = $_POST['code_postal'];
    $compte_admin = isset($_POST['compte_admin']) ? 1 : 0;

    if (
        empty($nom) || empty($prenom) || empty($adresse) ||
        empty($code_postal) || empty($email)
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

    if (empty($error_message)) {
        $stmt = $bdd->prepare("UPDATE utilisateurs SET nom = ?, prenom = ?, adresse = ?, code_postal = ?, compte_admin = ? WHERE adresse_mail = ?");
        $stmt->execute([$nom, $prenom, $adresse, $code_postal, $compte_admin, $email]);

        header("Location: dashboard.php");
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
        <title>Modifier utilisateur</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    </head>
    <body>
        <div id="navBar"></div>
        <div class="container">
            <div id="controlPanel" class="controlPanel"></div>
            <main>
                <h1>Modifier utilisateur</h1>

                <div id="form-error">
                    <span class="icon">⚠️</span>
                    <span id="error-message"></span>
                </div>

                <form method="POST" class="form-container">
                    <label>Nom</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>">

                    <label>Prénom</label>
                    <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>">

                    <label>Adresse</label>
                    <input type="text" name="adresse" value="<?= htmlspecialchars($user['adresse']) ?>">

                    <label>Code Postal</label>
                    <input type="text" name="code_postal" value="<?= htmlspecialchars($user['code_postal']) ?>">

                    <div class="compte-admin-checkbox">
                        <label>
                            <span>Compte admin</span>
                            <input type="checkbox" name="compte_admin" <?= $user['compte_admin'] ? 'checked' : '' ?>>
                        </label>
                    </div>
                    <button type="submit">Enregistrer</button>
                    <a href="dashboard.php" class="cp-btn">Annuler</a>
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
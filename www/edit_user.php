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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $adresse = $_POST['adresse'];
    $code_postal = $_POST['code_postal'];
    $compte_admin = isset($_POST['compte_admin']) ? 1 : 0;

    $stmt = $bdd->prepare("UPDATE utilisateurs SET nom = ?, prenom = ?, adresse = ?, code_postal = ?, compte_admin = ? WHERE adresse_mail = ?");
    $stmt->execute([$nom, $prenom, $adresse, $code_postal, $compte_admin, $email]);

    header("Location: dashboard.php");
    exit;
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
                <form method="POST" class="form-container">
                    <label>Nom</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>

                    <label>Prénom</label>
                    <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>

                    <label>Adresse</label>
                    <input type="text" name="adresse" value="<?= htmlspecialchars($user['adresse']) ?>" required>

                    <label>Code Postal</label>
                    <input type="text" name="code_postal" value="<?= htmlspecialchars($user['code_postal']) ?>" required>

                    <div class="compte-admin-checkbox">
                        <label>
                            <span>Compte admin</span>
                            <input type="checkbox" name="compte_admin" <?= $user['compte_admin'] ? 'checked' : '' ?>>
                        </label>
                    </div>
                    <button type="submit">Enregistrer</button>
                    <a href="dashboard.php" class="cp-btn">Annuler</a>
                </form>
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

<?php
require_once "../bdd/connexion_bdd.php";
session_start();

if (!isset($_SESSION['user']) || !$_SESSION['user']['compte_admin']) {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$id = $_GET['id'];

$stmt = $bdd->prepare("SELECT * FROM equipements_sportifs WHERE id = ?");
$stmt->execute([$id]);
$equip = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$equip) {
    echo "Équipement introuvable.";
    exit;
}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $type_equipement = $_POST['type_equipement'];
    $proprietaire = $_POST['proprietaire'];
    $adresse = $_POST['adresse'];
    $commune = $_POST['commune'];

    if (empty($nom) || empty($type_equipement) || empty($proprietaire) || empty($adresse) || empty($commune)) {
        $error_message="Veuillez saisir toutes les données.";
    }

    if (strlen($nom) > 100 || strlen($type_equipement) > 100 || strlen($proprietaire) > 100 || strlen($adresse) > 200 || strlen($commune) > 100) {
        $error_message = "Un des champs dépasse la longueur maximale autorisée.";
    }

    if (empty($error_message)) {
        $stmt = $bdd->prepare("UPDATE equipements_sportifs SET nom = ?, type_equipement = ?, proprietaire = ?, adresse = ?, commune = ? WHERE id = ?");
        $stmt->execute([$nom, $type_equipement, $proprietaire, $adresse, $commune, $id]);

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
        <title>Modifier équipement</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    </head>
    <body>
        <div id="navBar"></div>
        <div class="container">
            <div id="controlPanel" class="controlPanel"></div>
            <main>
                <h1>Modifier équipement</h1>

                <div id="form-error">
                    <span class="icon">⚠️</span>
                    <span id="error-message"></span>
                </div>

                <form method="POST" class="form-container">
                    <label>Nom</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($equip['nom']) ?>" required>

                    <label>Type d'équipement</label>
                    <input type="text" name="type_equipement" value="<?= htmlspecialchars($equip['type_equipement']) ?>">

                    <label>Propriétaire</label>
                    <input type="text" name="proprietaire" value="<?= htmlspecialchars($equip['proprietaire']) ?>">

                    <label>Adresse</label>
                    <input type="text" name="adresse" value="<?= htmlspecialchars($equip['adresse']) ?>">

                    <label>Commune</label>
                    <input type="text" name="commune" value="<?= htmlspecialchars($equip['commune']) ?>">

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
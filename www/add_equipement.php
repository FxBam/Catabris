<?php
require_once "../bdd/connexion_bdd.php";
session_start();

if (!isset($_SESSION['user']) || !$_SESSION['user']['compte_admin']) {
    header("Location: index.php");
    exit;
}

$error_message = "";
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $type_equipement = trim($_POST['type_equipement'] ?? '');
    $commune = trim($_POST['commune'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $proprietaire_principal_type = trim($_POST['proprietaire_principal_type'] ?? '');
    $sanitaires = isset($_POST['sanitaires']) ? 'Oui' : 'Non';
    $acces_handi_mobilite = !empty($_POST['acces_handi_mobilite']) ? $_POST['acces_handi_mobilite'] : null;
    $acces_handi_sensoriel = !empty($_POST['acces_handi_sensoriel']) ? $_POST['acces_handi_sensoriel'] : null;

    // Validation des champs obligatoires
    if (empty($nom) || empty($type_equipement) || empty($commune) || empty($adresse)) {
        $error_message = "Veuillez saisir toutes les données obligatoires (nom, type, commune, adresse).";
    }

    // Validation des longueurs
    if (empty($error_message)) {
        if (strlen($nom) > 255 || strlen($type_equipement) > 100 || strlen($commune) > 255) {
            $error_message = "Un des champs dépasse la longueur maximale autorisée.";
        }
        if (!empty($proprietaire_principal_type) && strlen($proprietaire_principal_type) > 100) {
            $error_message = "Le champ propriétaire dépasse la longueur maximale autorisée.";
        }
        if (!empty($acces_handi_mobilite) && strlen($acces_handi_mobilite) > 100) {
            $error_message = "Le champ accès handicapé mobilité dépasse la longueur maximale autorisée (100 caractères).";
        }
        if (!empty($acces_handi_sensoriel) && strlen($acces_handi_sensoriel) > 100) {
            $error_message = "Le champ accès handicapé sensoriel dépasse la longueur maximale autorisée (100 caractères).";
        }
    }

    // Géocodage de l'adresse
    $coordonnees_y = null;
    $coordonnees_x = null;
    $coordonnees = null;

    if (empty($error_message)) {
        // Utilisation de l'API Nominatim (OpenStreetMap) pour le géocodage
        $adresse_complete = urlencode($adresse . ', ' . $commune . ', France');
        $url = "https://nominatim.openstreetmap.org/search?q={$adresse_complete}&format=json&limit=1";
        
        $options = [
            'http' => [
                'header' => "User-Agent: Catabris/1.0\r\n"
            ]
        ];
        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        
        if ($response !== false) {
            $data = json_decode($response, true);
            if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
                $coordonnees_y = floatval($data[0]['lat']); // latitude
                $coordonnees_x = floatval($data[0]['lon']); // longitude
                $coordonnees = $coordonnees_y . ', ' . $coordonnees_x;
            } else {
                $error_message = "Impossible de trouver les coordonnées pour cette adresse. Veuillez vérifier l'adresse saisie.";
            }
        } else {
            $error_message = "Erreur lors de la connexion au service de géocodage. Veuillez réessayer.";
        }
    }

    // Insertion en base de données
    if (empty($error_message)) {
        // Génération d'un ID unique
        $new_id = 'E' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 12));
        
        $stmt = $bdd->prepare("INSERT INTO equipements_sportifs (
            id, nom, type_equipement, commune, proprietaire_principal_type, 
            sanitaires, acces_handi_mobilite, acces_handi_sensoriel, 
            coordonnees, coordonnees_y, coordonnees_x, creation_dt, maj_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        
        try {
            $stmt->execute([
                $new_id, $nom, $type_equipement, $commune, $proprietaire_principal_type,
                $sanitaires, $acces_handi_mobilite, $acces_handi_sensoriel,
                $coordonnees, $coordonnees_y, $coordonnees_x
            ]);
            
            header("Location: dashboard.php?success=1");
            exit;
        } catch (PDOException $e) {
            $error_message = "Erreur lors de l'insertion : " . $e->getMessage();
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
        <title>Ajouter un équipement</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    </head>
    <body>
        <div id="navBar"></div>
        <div class="container">
            <div id="controlPanel" class="controlPanel"></div>
            <main>
                <h1>Ajouter un équipement</h1>

                <div id="form-error">
                    <span class="icon">⚠️</span>
                    <span id="error-message"></span>
                </div>

                <form method="POST" class="form-container">
                    <label>Nom *</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>

                    <label>Type d'équipement *</label>
                    <input type="text" name="type_equipement" value="<?= htmlspecialchars($_POST['type_equipement'] ?? '') ?>" required>

                    <label>Adresse *</label>
                    <input type="text" name="adresse" placeholder="Ex: 12 rue de la République" value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>" required>

                    <label>Commune *</label>
                    <input type="text" name="commune" value="<?= htmlspecialchars($_POST['commune'] ?? '') ?>" required>

                    <label>Propriétaire principal</label>
                    <input type="text" name="proprietaire_principal_type" value="<?= htmlspecialchars($_POST['proprietaire_principal_type'] ?? '') ?>">

                    <label>Sanitaires disponibles</label>
                    <input type="checkbox" name="sanitaires" <?= isset($_POST['sanitaires']) ? 'checked' : '' ?>>

                    <label>Accès handicapé mobilité</label>
                    <input type="text" name="acces_handi_mobilite" value="<?= htmlspecialchars($_POST['acces_handi_mobilite'] ?? '') ?>">

                    <label>Accès handicapé sensoriel</label>
                    <input type="text" name="acces_handi_sensoriel" value="<?= htmlspecialchars($_POST['acces_handi_sensoriel'] ?? '') ?>">

                    <p style="font-size: 12px; color: #666; margin-top: 10px;">* Champs obligatoires</p>
                    <p style="font-size: 12px; color: #666;">Les coordonnées GPS seront automatiquement calculées à partir de l'adresse.</p>

                    <button type="submit">Ajouter l'équipement</button>
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
                            showError("<?= addslashes($error_message) ?>");
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

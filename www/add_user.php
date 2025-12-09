<?php
require_once "../bdd/connexion_bdd.php";
session_start();

if (!isset($_SESSION['user']) || !$_SESSION['user']['compte_admin']) {
    header("Location: index.php");
    exit;
}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $code_postal = trim($_POST['code_postal'] ?? '');
    $email = trim($_POST['adresse_mail'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $mot_de_passe_confirm = $_POST['mot_de_passe_confirm'] ?? '';
    $compte_admin = isset($_POST['compte_admin']) ? 1 : 0;

    if (empty($nom) || empty($prenom) || empty($adresse) || empty($code_postal) || empty($email) || empty($mot_de_passe)) {
        $error_message = "Veuillez remplir tous les champs obligatoires.";
    }

    elseif (strlen($nom) > 50 || strlen($prenom) > 50 || strlen($adresse) > 200 || strlen($email) > 100) {
        $error_message = "Un des champs dépasse la longueur maximale autorisée.";
    }

    elseif (ctype_digit($nom) || ctype_digit($prenom)) {
        $error_message = "Le nom et le prénom ne doivent pas contenir uniquement des chiffres.";
    }

    elseif (!ctype_digit($code_postal) || strlen($code_postal) != 5) {
        $error_message = "Code postal invalide (5 chiffres requis).";
    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Adresse email invalide.";
    }

    elseif ($mot_de_passe !== $mot_de_passe_confirm) {
        $error_message = "Les mots de passe ne correspondent pas.";
    }

    if (empty($error_message)) {
        $stmt = $bdd->prepare("SELECT COUNT(*) FROM utilisateurs WHERE adresse_mail = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $error_message = "Cette adresse email est déjà utilisée.";
        }
    }

    if (empty($error_message)) {
        $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        
        $stmt = $bdd->prepare("INSERT INTO utilisateurs (nom, prenom, adresse, code_postal, adresse_mail, mot_de_passe, compte_admin) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        try {
            $stmt->execute([$nom, $prenom, $adresse, $code_postal, $email, $mot_de_passe_hash, $compte_admin]);
            
            header("Location: dashboard.php?success=user_added");
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
        <title>Ajouter un utilisateur</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    </head>
    <body>
        <div id="navBar"></div>
        <div class="container">
            <div id="controlPanel" class="controlPanel"></div>
            <main>
                <h1>Ajouter un utilisateur</h1>

                <div id="form-error">
                    <span class="icon">⚠️</span>
                    <span id="error-message"></span>
                </div>

                <form method="POST" class="form-container">
                    <label>Nom *</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>

                    <label>Prénom *</label>
                    <input type="text" name="prenom" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required>

                    <label>Adresse email *</label>
                    <input type="email" name="adresse_mail" value="<?= htmlspecialchars($_POST['adresse_mail'] ?? '') ?>" required>

                    <label>Mot de passe *</label>
                    <input type="password" name="mot_de_passe" required>

                    <label>Confirmer le mot de passe *</label>
                    <input type="password" name="mot_de_passe_confirm" required>

                    <label>Adresse *</label>
                    <input type="text" name="adresse" value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>" required>

                    <label>Code Postal *</label>
                    <input type="text" name="code_postal" maxlength="5" value="<?= htmlspecialchars($_POST['code_postal'] ?? '') ?>" required>

                    <div class="compte-admin-checkbox">
                        <label>
                            <span>Compte admin</span>
                            <input type="checkbox" name="compte_admin" <?= isset($_POST['compte_admin']) ? 'checked' : '' ?>>
                        </label>
                    </div>

                    <p style="font-size: 12px; color: #666; margin-top: 10px;">* Champs obligatoires</p>

                    <button type="submit">Ajouter l'utilisateur</button>
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

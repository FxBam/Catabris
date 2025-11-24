<?php
require_once "../bdd/connexion_bdd.php";
session_start();
?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = strtolower($_POST['nom']);
    $prenom = strtolower($_POST['prenom']);
    $adresse = strtolower($_POST['adresse']);
    $code_postal = $_POST['code-postal'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    if (empty($nom) || empty($prenom) || empty($adresse) || empty($code_postal) || empty($email) || empty($password) || empty($confirm_password)) {
        echo "<script>alert('Veuillez remplir tous les champs.');</script>";
        exit;
    }

    if (strlen($nom) > 50 || strlen($prenom) > 50 || strlen($adresse) > 200 || strlen($email) > 100) {
        echo "<script>alert('Le nom et le prénom ne doivent pas dépasser 50 caractères.');</script>";
        exit;
    }

    if (ctype_digit($nom) || ctype_digit($prenom)) {
        echo "<script>alert('Le nom et le prénom ne doivent pas contenir de chiffres.');</script>";
        exit;
    }
    
    if (!ctype_digit($code_postal)|| strlen($code_postal) != 5) {
        echo "<script>alert('Code postal invalide.');</script>";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Adresse email invalide.');</script>";
        exit;
    }
    
    $stmt = $bdd->prepare("SELECT COUNT(*) FROM utilisateurs WHERE adresse_mail = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        echo "<script>alert('Cette adresse email est déjà utilisée.');</script>";
        exit;
    }


    if ($password !== $confirm_password) {
        echo "<script>alert('Les mots de passe ne correspondent pas.');</script>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $bdd->prepare("INSERT INTO utilisateurs (nom, prenom, adresse, code_postal, adresse_mail, mot_de_passe) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$nom, $prenom, $adresse, $code_postal, $email, $hashed_password])) {
            echo "<script>alert('Inscription réussie ! Vous pouvez maintenant vous connecter.'); window.location.href='connexion.php';</script>";
        } else {
            echo "<script>alert('Erreur lors de l\'inscription. Veuillez réessayer.');</script>";
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
        <title>Inscription</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    </head>
    <body>
        <div id="navBar"></div>
        <div class="container">
            <div id="controlPanel" class="controlPanel"></div>
            <main>
                <h1>Inscription</h1>
                <form action="#" method="POST" class="form-container">
                    <label for="nom">Nom :</label>
                    <input type="text" id="nom" name="nom" required>

                    <label for="prenom">Prénom :</label>
                    <input type="text" id="prenom" name="prenom" required>

                    <label for="adresse">Adresse :</label>
                    <input type="text" id="adresse" name="adresse" required>

                    <label for="code-postal">Code Postal :</label>
                    <input type="text" id="code-postal" name="code-postal" required>

                    <label for="email">Adresse Email :</label>
                    <input type="email" id="email" name="email" required>

                    <label for="password">Mot de passe :</label>
                    <input type="password" id="password" name="password" required>

                    <label for="confirm-password">Confirmer le mot de passe :</label>
                    <input type="password" id="confirm-password" name="confirm-password" required>

                    <button type="submit">S'inscrire</button>
                </form>
                <p>Vous avez déjà un compte ? <a href="connexion.php">Connectez-vous</a></p>
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
<?php
require_once "../bdd/connexion_bdd.php";
?>

<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
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
            $error = "Adresse email ou mot de passe incorrect.";
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
                <form action="#" method="POST" class="form-container">
                    <label for="email">Adresse Email :</label>
                    <input type="email" id="email" name="email" required>

                    <label for="password">Mot de passe :</label>
                    <input type="password" id="password" name="password" required>

                    <p class="mdpOublie">
                        <a href="#">Mot de passe oublié ?</a>
                    <p>

                    <button type="submit">Se connecter</button>
                </form>
                <p>Vous avez déjà un compte ? <a href="inscription.php">Inscrivez-vous</a></p>
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
<?php
require_once "../bdd/connexion_bdd.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Connexion</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
</head>
<body>
    <div id="navBar"></div>
    <main>
        <h1>Connexion</h1>
        <form action="#" method="POST" class="form-container">
            <label for="email">Adresse Email :</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Se connecter</button>
        </form>
        <p><a href="#">Mot de passe oublié ?</a></p>
    </main>
    <script>
        $(function() {
            $("#navBar").load("navBar.php");
        });
    </script>
</body>
</html>
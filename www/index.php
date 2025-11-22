<?php
require_once "../bdd/connexion_bdd.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Catabris</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
</head>
<body>
    <div id="navBar"></div>
    <main>
        <h1>Bienvenue sur Catabris</h1>
    </main>
    <script>
        $(function() {
            $("#navBar").load("navBar.php");
        });
    </script>
</body>
</html>
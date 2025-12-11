<?php
require_once "../bdd/connexion_bdd.php";
session_start();
?>

<nav>
    <div class="logo">
        <a>Catabris</a>
    </div>
    <div class="nav-links">
        <a href="index.php">Accueil</a>
        <?php if (isset($_SESSION['user'])): ?>
                <a href="deconnexion.php">Déconnexion</a>
        <?php else: ?>
                <a href="connexion.php">Connexion</a>
                <a href="inscription.php">Inscription</a>
        <?php endif; ?>
    </div>
</nav>

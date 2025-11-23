<?php
require_once "../bdd/connexion_bdd.php";
session_start();
?>

<div class="controlPanelContent">
        <ul>
            <li><a href="index.php" title="Tableau de bord"><i class="fa fa-home"></i></a></li>
            <?php if (isset($_SESSION['user'])): ?>
                <li><a href="profil.php" title="Profil"><i class="fa fa-user"></i></a></li>
                <li><a href="#" title="favoris"><i class="fa fa-bell"></i></a></li>
            <?php else: ?>
                <li><a href="connexion.php" title="Profil"><i class="fa fa-user"></i></a></li>
                <li><a href="connexion.php" title="favoris"><i class="fa fa-bell"></i></a></li>
            <?php endif; ?>
            <li><a href="contact.php" title="Contact"><i class="fa fa-envelope"></i></a></li>
        </ul>
</div>
<?php
require_once "../bdd/connexion_bdd.php";
session_start();

$user_id = null;
if (isset($_SESSION['user'])) {
    $compte_admin = $_SESSION['user']['compte_admin'];
}
?>

<div class="controlPanelContent">
    <ul>
        <li><a href="index.php" title="Accueil"><i class="fa fa-home"></i></a></li>

        <?php if (isset($_SESSION['user']) && !empty($user_id)): ?>
            <li><a href="profil.php" title="Profil"><i class="fa fa-user"></i></a></li>
            <li><a href="#" title="Favoris"><i class="fa fa-bell"></i></a></li>
        <?php else: ?>
            <li><a href="connexion.php" title="Profil"><i class="fa fa-user"></i></a></li>
            <li><a href="connexion.php" title="Favoris"><i class="fa fa-bell"></i></a></li>
        <?php endif; ?>
        <li><a href="contact.php" title="Contact"><i class="fa fa-envelope"></i></a></li>
        <?php if (isset($_SESSION['user']) && $compte_admin == 1): ?>
            <li><a href="dashboard.php" title="Dashboard"><i class="fa fa-sign-out-alt"></i></a></li>
        <?php endif; ?>
    </ul>
</div>

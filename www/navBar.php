<?php
require_once __DIR__ . '/../load_env.php';
require_once __DIR__ . '/../bdd/connexion_bdd.php';
session_start();

// Expose API base URL to client-side JS (empty = use relative paths)
$apiBase = getenv('API_BASE_URL') ?: '';
?>
<script>window.API_BASE_URL = "<?= htmlspecialchars($apiBase, ENT_QUOTES) ?>";</script>

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

<?php
require_once "../bdd/connexion_bdd.php";
session_start();

if (!isset($_SESSION['user']) || !$_SESSION['user']['compte_admin']) {
    header("Location: index.php");
    exit;
}

$stmt = $bdd->query("SELECT COUNT(*) AS total_equipements FROM equipements_sportifs");
$stats_equipements = $stmt->fetch(PDO::FETCH_ASSOC)['total_equipements'];

$stmt = $bdd->query("SELECT COUNT(*) AS total_utilisateurs FROM utilisateurs");
$stats_utilisateurs_connecte = $stmt->fetch(PDO::FETCH_ASSOC)['total_utilisateurs'];

$stmt = $bdd->query("SELECT COUNT(*) AS total_vues FROM vues_site");
$stats_vues_site = $stmt->fetch(PDO::FETCH_ASSOC)['total_vues'];

$equipements_result = [];
$users_result = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['search_equipement'])) {
    $search_equip = '%' . $_POST['search_equipement'] . '%';
    $stmt = $bdd->prepare("SELECT * FROM equipements_sportifs WHERE nom LIKE ? or commune LIKE ?");
    $stmt->execute([$search_equip, $search_equip]);
    $equipements_result = $stmt->fetchAll(PDO::FETCH_ASSOC);    
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['search_user'])) {
    $search_user = '%' . $_POST['search_user'] . '%';
    $stmt = $bdd->prepare("SELECT * FROM utilisateurs WHERE nom LIKE ? OR prenom LIKE ?");
    $stmt->execute([$search_user, $search_user]);
    $users_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (empty($users_result)) {
    $stmt = $bdd->query("SELECT * FROM utilisateurs");
    $users_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <title>Dashboard Admin</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    </head>
    <body class="dashboard-page">
        <div id="navBar"></div>
        <div class="container">
            <div id="controlPanel" class="controlPanel"></div>
            <main>
                <h1>Dashboard Admin</h1>
                <div class="dashboard-stats">
                    <div class="stat-box">
                        <h3>Équipements</h3>
                        <p><?= $stats_equipements ?></p>
                    </div>
                    <div class="stat-box">
                        <h3>Connexions</h3>
                        <p><?= $stats_utilisateurs_connecte ?></p>
                    </div>
                    <div class="stat-box">
                        <h3>Vues du site</h3>
                        <p><?= $stats_vues_site ?></p>
                    </div>
                </div>

                <div class="dashboard-modification">

                    <div class="search-section">
                        <h2>Recherche d'équipements</h2>
                        <form method="POST">
                            <input type="text" name="search_equipement" placeholder="Nom de l'équipement">
                            <button type="submit">Rechercher</button>
                        </form>
                        <?php if (!empty($equipements_result)): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Type</th>
                                        <th>Commune</th>
                                        <th>Modifier</th>
                                        <th>Supprimer</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($equipements_result as $equip): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($equip['id']) ?></td>
                                        <td><?= htmlspecialchars($equip['nom']) ?></td>
                                        <td><?= htmlspecialchars($equip['type_equipement']) ?></td>
                                        <td><?= htmlspecialchars($equip['commune']) ?></td>
                                        <td><a href="edit_equipement.php?id=<?= urlencode($equip['id']) ?>">Modifier</a></td>
                                        <td><a<?= urlencode($equip['id']) ?>>Supprimer</a></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                    <div class="search-section">
                        <h2>Recherche d'utilisateurs</h2>
                        <form method="POST">
                            <input type="text" name="search_user" placeholder="Nom ou prénom">
                            <button type="submit">Rechercher</button>
                        </form>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Email</th>
                                    <th>Adresse</th>
                                    <th>Code Postal</th>
                                    <th>Admin</th>
                                    <th>Modifier</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($users_result as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['nom']) ?></td>
                                    <td><?= htmlspecialchars($user['prenom']) ?></td>
                                    <td><?= htmlspecialchars($user['adresse_mail']) ?></td>
                                    <td><?= htmlspecialchars($user['adresse']) ?></td>
                                    <td><?= htmlspecialchars($user['code_postal']) ?></td>
                                    <td><?= htmlspecialchars($user['compte_admin'] ? 'oui' : 'non') ?></td>
                                    <td><a href="edit_user.php?email=<?= urlencode($user['adresse_mail']) ?>">Modifier</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(function() {
                $("#navBar").load("navBar.php");
                $("#controlPanel").load("controlPanel.php");
            });
        </script>
    </body>
</html>
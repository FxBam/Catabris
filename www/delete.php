<?php
require_once "../bdd/connexion_bdd.php";
session_start();

if (!isset($_SESSION['user']) || !$_SESSION['user']['compte_admin']) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit;
}

$type = $_POST['type'] ?? '';

try {
    if ($type === 'equip') {
        $id = $_POST['id'] ?? '';
        if (!ctype_digit((string)$id)) {
            header("Location: dashboard.php");
            exit;
        }
        $stmt = $bdd->prepare("DELETE FROM equipements_sportifs WHERE id = ?");
        $stmt->execute([$id]);
    } elseif ($type === 'user') {
        $email = $_POST['email'] ?? '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: dashboard.php");
            exit;
        }
        $stmt = $bdd->prepare("DELETE FROM utilisateurs WHERE adresse_mail = ?");
        $stmt->execute([$email]);
    }
} catch (Exception $e) {
    echo "Erreur lors de la suppression : " . $e->getMessage();
    exit;
}

header("Location: dashboard.php");
exit;

?>

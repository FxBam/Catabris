<?php
require_once "../bdd/connexion_bdd.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION = [];
    session_destroy();

    header("Location: index.php");
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>

<?php
$host = "mysql.infuseting.fr";
$dbname = "catabris";
$user = "catabris";
$pass = '';

try {
    $bdd = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
} catch (PDOException $e) {
    die($e->getMessage());
}
?>

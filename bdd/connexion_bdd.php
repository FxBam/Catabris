<?php
$host = "127.0.0.1";
$dbname = "catabris";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
} catch (PDOException $e) {
    die($e->getMessage());
}
?>
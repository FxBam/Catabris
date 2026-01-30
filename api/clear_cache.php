<?php
header('Content-Type: application/json; charset=utf-8');

session_start();
require_once dirname(__DIR__) . "/bdd/connexion_bdd.php";

// Only allow admin users to clear server cache
if (!isset($_SESSION['user']) || empty($_SESSION['user']['compte_admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

$cacheDir = __DIR__ . '/../tmp_cache';
if (!is_dir($cacheDir)) {
    echo json_encode(['success' => true, 'deleted' => 0, 'message' => 'No cache directory']);
    exit;
}

$files = glob($cacheDir . '/*.json');
$deleted = 0;
foreach ($files as $f) {
    if (@unlink($f)) $deleted++;
}

echo json_encode(['success' => true, 'deleted' => $deleted]);

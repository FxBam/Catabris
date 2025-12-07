<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit();
}

require_once "../bdd/connexion_bdd.php";

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? intval($input['id']) : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID invalide']);
    exit();
}

try {
    $stmt = $bdd->prepare("UPDATE equipements_sportifs SET nb_remplie = COALESCE(nb_remplie, 0) + 1 WHERE id = :id");
    $stmt->execute([':id' => $id]);
    
    if ($stmt->rowCount() > 0) {
        $stmt = $bdd->prepare("SELECT nb_remplie, nb_capacite FROM equipements_sportifs WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'id' => $id,
            'nb_remplie' => intval($row['nb_remplie']),
            'nb_capacite' => intval($row['nb_capacite'])
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Équipement non trouvé']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
}

<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once dirname(__DIR__) . "/bdd/connexion_bdd.php";

try {
    $bdd->query("SELECT 1 FROM urgences LIMIT 1");
} catch (PDOException $e) {
    $bdd->exec("CREATE TABLE urgences (
        id INT AUTO_INCREMENT PRIMARY KEY,
        commune VARCHAR(100) NOT NULL,
        activateur_email VARCHAR(100) NOT NULL,
        activateur_nom VARCHAR(100) NOT NULL,
        date_activation DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_commune (commune)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $bdd->query("SELECT id, commune, activateur_email, activateur_nom, date_activation FROM urgences ORDER BY date_activation DESC");
        $urgences = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'urgences' => $urgences], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'error' => 'Non connecté'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['commune']) || empty(trim($input['commune']))) {
            echo json_encode(['success' => false, 'error' => 'Commune requise'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $commune = trim($input['commune']);
        $userEmail = isset($_SESSION['email']) ? $_SESSION['email'] : $_SESSION['user']['adresse_mail'];
        $userName = $_SESSION['user']['prenom'] . ' ' . $_SESSION['user']['nom'];
        
        $stmt = $bdd->prepare("INSERT INTO urgences (commune, activateur_email, activateur_nom) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE activateur_email = VALUES(activateur_email), activateur_nom = VALUES(activateur_nom), date_activation = NOW()");
        $stmt->execute([$commune, $userEmail, $userName]);
        
        echo json_encode(['success' => true, 'message' => 'Mode urgence activé pour ' . $commune], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'error' => 'Non connecté'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['id'])) {
            echo json_encode(['success' => false, 'error' => 'ID requis'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $stmt = $bdd->prepare("DELETE FROM urgences WHERE id = ?");
        $stmt->execute([$input['id']]);
        
        echo json_encode(['success' => true, 'message' => 'Mode urgence désactivé'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur BDD'], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur'], JSON_UNESCAPED_UNICODE);
}

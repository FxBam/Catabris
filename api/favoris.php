<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once dirname(__DIR__) . "/bdd/connexion_bdd.php";

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Non connecté'], JSON_UNESCAPED_UNICODE);
    exit;
}

$userEmail = isset($_SESSION['email']) ? $_SESSION['email'] : (isset($_SESSION['user']['adresse_mail']) ? $_SESSION['user']['adresse_mail'] : null);

if (!$userEmail) {
    echo json_encode(['success' => false, 'error' => 'Email non trouvé dans la session'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    try {
        $bdd->query("SELECT favoris FROM utilisateurs LIMIT 1");
    } catch (PDOException $e) {
        $bdd->exec("ALTER TABLE utilisateurs ADD COLUMN favoris TEXT DEFAULT NULL");
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $bdd->prepare("SELECT favoris FROM utilisateurs WHERE adresse_mail = ?");
        $stmt->execute([$userEmail]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $favoris = [];
        if ($result && isset($result['favoris']) && $result['favoris']) {
            $favoris = json_decode($result['favoris'], true) ?? [];
        }
        
        echo json_encode(['success' => true, 'favoris' => $favoris], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['equipement_id']) || !isset($input['action'])) {
            echo json_encode(['success' => false, 'error' => 'Paramètres manquants'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $equipementId = $input['equipement_id'];
        $action = $input['action'];
        
        $stmt = $bdd->prepare("SELECT favoris FROM utilisateurs WHERE adresse_mail = ?");
        $stmt->execute([$userEmail]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            echo json_encode(['success' => false, 'error' => 'Utilisateur non trouvé'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $favoris = [];
        if (isset($result['favoris']) && $result['favoris']) {
            $favoris = json_decode($result['favoris'], true) ?? [];
        }
        
        if ($action === 'add') {
            if (!in_array($equipementId, $favoris)) {
                $favoris[] = $equipementId;
            }
        } elseif ($action === 'remove') {
            $favoris = array_values(array_filter($favoris, fn($id) => $id !== $equipementId));
        }
        
        $jsonFavoris = json_encode($favoris);
        $stmt = $bdd->prepare("UPDATE utilisateurs SET favoris = ? WHERE adresse_mail = ?");
        $stmt->execute([$jsonFavoris, $userEmail]);
        
        echo json_encode(['success' => true, 'favoris' => $favoris], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur BDD'], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur'], JSON_UNESCAPED_UNICODE);
}

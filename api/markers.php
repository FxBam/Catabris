<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once dirname(__DIR__) . "/bdd/connexion_bdd.php";

try {
    $limit = min(5000, max(1, intval($_GET['limit'] ?? 3000)));
    $markers = [];
    
    $minLat = isset($_GET['minLat']) ? floatval($_GET['minLat']) : 41.0;
    $maxLat = isset($_GET['maxLat']) ? floatval($_GET['maxLat']) : 51.5;
    $minLon = isset($_GET['minLon']) ? floatval($_GET['minLon']) : -5.5;
    $maxLon = isset($_GET['maxLon']) ? floatval($_GET['maxLon']) : 10.0;
    
    $query = isset($_GET['q']) && !empty($_GET['q']) ? '%' . $_GET['q'] . '%' : null;
    
    $gridSize = 6;
    $pointsPerCell = max(1, intval($limit / ($gridSize * $gridSize)));
    
    $latStep = ($maxLat - $minLat) / $gridSize;
    $lonStep = ($maxLon - $minLon) / $gridSize;
    
    for ($i = 0; $i < $gridSize; $i++) {
        for ($j = 0; $j < $gridSize; $j++) {
            $cellMinLat = $minLat + ($i * $latStep);
            $cellMaxLat = $minLat + (($i + 1) * $latStep);
            $cellMinLon = $minLon + ($j * $lonStep);
            $cellMaxLon = $minLon + (($j + 1) * $lonStep);
            
            $sql = "SELECT id, coordonnees_y, coordonnees_x FROM equipements_sportifs 
                    WHERE coordonnees_y BETWEEN ? AND ? 
                    AND coordonnees_x BETWEEN ? AND ?";
            $params = [$cellMinLat, $cellMaxLat, $cellMinLon, $cellMaxLon];
            
            if ($query) {
                $sql .= " AND (nom LIKE ? OR commune LIKE ?)";
                $params[] = $query;
                $params[] = $query;
            }
            
            $sql .= " ORDER BY RAND() LIMIT " . $pointsPerCell;
            
            $stmt = $bdd->prepare($sql);
            $stmt->execute($params);
            
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $markers[] = [
                    'id' => $row[0],
                    'latitude' => (float)$row[1],
                    'longitude' => (float)$row[2]
                ];
            }
        }
    }
    
    shuffle($markers);
    
    echo json_encode(['success' => true, 'markers' => $markers]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

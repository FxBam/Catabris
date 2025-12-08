<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once dirname(__DIR__) . "/bdd/connexion_bdd.php";

try {
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $id = $_GET['id'];
        
        $sql = "SELECT id, nom, type_equipement, commune, 
                       coordonnees_y, coordonnees_x, proprietaire_principal_type, 
                       sanitaires, acces_handi_mobilite, acces_handi_sensoriel, creation_dt, nb_remplie, nb_capacite 
                FROM equipements_sportifs WHERE id = ? LIMIT 1";
        
        $stmt = $bdd->prepare($sql);
        $stmt->execute([$id]);
        $equip = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($equip) {
            $formatted = [
                'id' => $equip['id'],
                'nom' => $equip['nom'],
                'type_equipement' => $equip['type_equipement'],
                'commune' => $equip['commune'],
                'latitude' => $equip['coordonnees_y'] ? (float)$equip['coordonnees_y'] : null,
                'longitude' => $equip['coordonnees_x'] ? (float)$equip['coordonnees_x'] : null,
                'proprietaire_principal_type' => $equip['proprietaire_principal_type'],
                'sanitaires' => $equip['sanitaires'],
                'acces_handi_mobilite' => $equip['acces_handi_mobilite'],
                'acces_handi_sensoriel' => $equip['acces_handi_sensoriel'],
                'creation_dt' => $equip['creation_dt'],
                'nb_remplie' => (int)($equip['nb_remplie'] ?? 0),
                'nb_capacite' => (int)($equip['nb_capacite'] ?? 0),
            ];
            echo json_encode(['success' => true, 'count' => 1, 'data' => [$formatted]]);
        } else {
            echo json_encode(['success' => false, 'count' => 0, 'data' => []]);
        }
        exit;
    }

    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(500, max(1, intval($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;

    $columns = "id, nom, type_equipement, commune, coordonnees_y, coordonnees_x, 
                proprietaire_principal_type, proprietaire_principal_nom, sanitaires, acces_handi_mobilite, acces_handi_sensoriel, 
                acces_libre, website, creation_dt, maj_date, nb_remplie, nb_capacite";
    
    $sql = "SELECT $columns FROM equipements_sportifs WHERE coordonnees_y IS NOT NULL AND coordonnees_x IS NOT NULL";
    $params = [];
    $countSql = "SELECT COUNT(*) as total FROM equipements_sportifs WHERE coordonnees_y IS NOT NULL AND coordonnees_x IS NOT NULL";

    if (isset($_GET['q']) && !empty($_GET['q'])) {
        $query = '%' . $_GET['q'] . '%';
        $sql .= " AND (nom LIKE ? OR commune LIKE ?)";
        $countSql .= " AND (nom LIKE ? OR commune LIKE ?)";
        $params[] = $query;
        $params[] = $query;
    }

    if (isset($_GET['minLat'], $_GET['maxLat'], $_GET['minLon'], $_GET['maxLon'])) {
        $minLat = floatval($_GET['minLat']);
        $maxLat = floatval($_GET['maxLat']);
        $minLon = floatval($_GET['minLon']);
        $maxLon = floatval($_GET['maxLon']);

        $sql .= " AND coordonnees_y BETWEEN ? AND ? AND coordonnees_x BETWEEN ? AND ?";
        $countSql .= " AND coordonnees_y BETWEEN ? AND ? AND coordonnees_x BETWEEN ? AND ?";
        $params[] = $minLat;
        $params[] = $maxLat;
        $params[] = $minLon;
        $params[] = $maxLon;
    }

    $totalCount = 0;
    if ($page > 1) {
        $countStmt = $bdd->prepare($countSql);
        $countStmt->execute($params);
        $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    $sql .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);

    $stmt = $bdd->prepare($sql);
    $stmt->execute($params);
    $equipements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formattedEquipements = [];
    foreach ($equipements as $equip) {
        $formattedEquipements[] = [
            'id' => $equip['id'] ?? null,
            'nom' => $equip['nom'] ?? null,
            'type_equipement' => $equip['type_equipement'] ?? null,
            'commune' => $equip['commune'] ?? null,
            'latitude' => !empty($equip['coordonnees_y']) ? floatval($equip['coordonnees_y']) : null,
            'longitude' => !empty($equip['coordonnees_x']) ? floatval($equip['coordonnees_x']) : null,
            'proprietaire_principal_type' => $equip['proprietaire_principal_type'] ?? null,
            'proprietaire_principal_nom' => $equip['proprietaire_principal_nom'] ?? null,
            'sanitaires' => $equip['sanitaires'] ?? null,
            'acces_handi_mobilite' => $equip['acces_handi_mobilite'] ?? null,
            'acces_handi_sensoriel' => $equip['acces_handi_sensoriel'] ?? null,
            'acces_libre' => $equip['acces_libre'] ?? null,
            'website' => $equip['website'] ?? null,
            'creation_dt' => $equip['creation_dt'] ?? null,
            'maj_date' => $equip['maj_date'] ?? null,
            'nb_remplie' => isset($equip['nb_remplie']) ? intval($equip['nb_remplie']) : 0,
            'nb_capacite' => isset($equip['nb_capacite']) ? intval($equip['nb_capacite']) : 0,
        ];
    }

    $totalPages = ceil($totalCount / $limit);

    $response = [
        'success' => true,
        'page' => $page,
        'limit' => $limit,
        'total_count' => $totalCount,
        'total_pages' => $totalPages,
        'count' => count($equipements),
        'data' => $formattedEquipements
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>

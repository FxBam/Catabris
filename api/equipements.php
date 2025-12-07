<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once dirname(__DIR__) . "/bdd/connexion_bdd.php";

try {
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(500, max(1, intval($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;

    $sql = "SELECT * FROM equipements_sportifs WHERE coordonnees_y IS NOT NULL AND coordonnees_x IS NOT NULL";
    $params = [];
    $countSql = "SELECT COUNT(*) as total FROM equipements_sportifs WHERE coordonnees_y IS NOT NULL AND coordonnees_x IS NOT NULL";

    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $sql .= " AND id = ?";
        $countSql .= " AND id = ?";
        $params[] = intval($_GET['id']);
    }

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

    $countStmt = $bdd->prepare($countSql);
    $countStmt->execute($params);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

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
            'code_postal' => $equip['code_postal'] ?? null,
            'adresse' => $equip['adresse'] ?? null,
            'latitude' => !empty($equip['coordonnees_y']) ? floatval($equip['coordonnees_y']) : null,
            'longitude' => !empty($equip['coordonnees_x']) ? floatval($equip['coordonnees_x']) : null,
            'proprietaire_principal_type' => $equip['proprietaire_principal_type'] ?? null,
            'proprietaire_principal_nom' => $equip['proprietaire_principal_nom'] ?? null,
            'sanitaires' => !empty($equip['sanitaires']),
            'acces_handi_mobilite' => !empty($equip['acces_handi_mobilite']),
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

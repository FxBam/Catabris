<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once dirname(__DIR__) . "/bdd/connexion_bdd.php";

try {
    if (!isset($_GET['q']) || empty($_GET['q'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Paramètre "q" requis'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $limit = min(50, max(1, intval($_GET['limit'] ?? 10)));
    $query = '%' . $_GET['q'] . '%';

    $sql = "SELECT id, nom, commune, type_equipement, coordonnees_y, coordonnees_x 
            FROM equipements_sportifs 
            WHERE (nom LIKE ? OR commune LIKE ?) AND coordonnees_y IS NOT NULL AND coordonnees_x IS NOT NULL";
    $params = [$query, $query];

    if (isset($_GET['minLat'], $_GET['maxLat'], $_GET['minLon'], $_GET['maxLon'])) {
        $minLat = floatval($_GET['minLat']);
        $maxLat = floatval($_GET['maxLat']);
        $minLon = floatval($_GET['minLon']);
        $maxLon = floatval($_GET['maxLon']);

        $sql .= " AND coordonnees_y BETWEEN ? AND ? AND coordonnees_x BETWEEN ? AND ?";
        $params[] = $minLat;
        $params[] = $maxLat;
        $params[] = $minLon;
        $params[] = $maxLon;
    }

    $sql .= " LIMIT " . intval($limit);

    $stmt = $bdd->prepare($sql);
    $stmt->execute($params);
    $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formattedSuggestions = [];
    foreach ($suggestions as $suggestion) {
        $formattedSuggestions[] = [
            'id' => $suggestion['id'],
            'label' => $suggestion['nom'] . ' - ' . $suggestion['commune'],
            'nom' => $suggestion['nom'],
            'commune' => $suggestion['commune'],
            'type' => $suggestion['type_equipement'],
            'lat' => !empty($suggestion['coordonnees_y']) ? floatval($suggestion['coordonnees_y']) : null,
            'lon' => !empty($suggestion['coordonnees_x']) ? floatval($suggestion['coordonnees_x']) : null,
        ];
    }

    echo json_encode([
        'success' => true,
        'count' => count($suggestions),
        'suggestions' => $formattedSuggestions
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>

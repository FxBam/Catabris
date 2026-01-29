<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Small server-side caching and compression improve API response time.
if (extension_loaded('zlib')) {
    if (ob_get_level() === 0) ob_start('ob_gzhandler');
}

require_once dirname(__DIR__) . "/bdd/connexion_bdd.php";

try {
    $limit = min(5000, max(1, intval($_GET['limit'] ?? 3000)));
    $markers = [];
    
    $minLat = isset($_GET['minLat']) ? floatval($_GET['minLat']) : 41.0;
    $maxLat = isset($_GET['maxLat']) ? floatval($_GET['maxLat']) : 51.5;
    $minLon = isset($_GET['minLon']) ? floatval($_GET['minLon']) : -5.5;
    $maxLon = isset($_GET['maxLon']) ? floatval($_GET['maxLon']) : 10.0;
    
    $query = isset($_GET['q']) && !empty($_GET['q']) ? '%' . $_GET['q'] . '%' : null;
    $commune = isset($_GET['commune']) && !empty($_GET['commune']) ? '%' . $_GET['commune'] . '%' : null;
    $typeEquip = isset($_GET['type']) && !empty($_GET['type']) ? $_GET['type'] : null;
    $pmr = isset($_GET['pmr']) && $_GET['pmr'] === '1';
    $sensoriel = isset($_GET['sensoriel']) && $_GET['sensoriel'] === '1';
    
    // Balanced distribution approach:
    // 1) compute a grid and points-per-cell
    // 2) fetch a bounded candidate set in one query (no ORDER BY RAND())
    // 3) bucket candidates into cells in PHP
    // 4) sample up to pointsPerCell per cell (randomly) and return
    $gridSize = 6;
    $pointsPerCell = max(1, intval($limit / ($gridSize * $gridSize)));

    $latStep = ($maxLat - $minLat) / $gridSize;
    $lonStep = ($maxLon - $minLon) / $gridSize;

    $params = [$minLat, $maxLat, $minLon, $maxLon];
    $sql = "SELECT id, coordonnees_y, coordonnees_x FROM equipements_sportifs
            WHERE coordonnees_y BETWEEN ? AND ?
            AND coordonnees_x BETWEEN ? AND ?";

    if ($query) {
        $sql .= " AND (nom LIKE ? OR commune LIKE ?)";
        $params[] = $query;
        $params[] = $query;
    }

    if ($commune) {
        $sql .= " AND commune LIKE ?";
        $params[] = $commune;
    }

    if ($typeEquip) {
        $sql .= " AND type_equipement = ?";
        $params[] = $typeEquip;
    }

    if ($pmr) {
        $sql .= " AND acces_handi_mobilite IS NOT NULL AND acces_handi_mobilite != ''";
    }

    if ($sensoriel) {
        $sql .= " AND acces_handi_sensoriel IS NOT NULL AND acces_handi_sensoriel != ''";
    }

    if (isset($_GET['urgence']) && $_GET['urgence'] === '1') {
        $sql .= " AND type_equipement = 'Salle'";
        $sql .= " AND acces_handi_mobilite IS NOT NULL AND acces_handi_mobilite != ''";
        $sql .= " AND acces_handi_sensoriel IS NOT NULL AND acces_handi_sensoriel != ''";
    }

    // Fetch a bounded candidate set. This keeps DB work to a single query
    // while providing enough variety to distribute over the grid.
    // Use ORDER BY RAND() on the filtered set so returned candidates are
    // spread across the entire bounding box (helps fill the whole map).
    // Keep the candidate set bounded to limit DB cost.
    $candidateMultiplier = 4; // multiplier for candidate set size
    $candidatesLimit = min(8000, max(intval($limit * $candidateMultiplier), $limit));
    $sql .= " ORDER BY RAND() LIMIT " . intval($candidatesLimit);

    // Server-side cache: key derived from query string and bounding box
    $cacheTtl = 2; // seconds
    $cacheDir = __DIR__ . '/../tmp_cache';
    if (!is_dir($cacheDir)) @mkdir($cacheDir, 0777, true);
    $cacheKey = 'markers_' . md5($_SERVER['QUERY_STRING'] ?? http_build_query($_GET));
    $cacheFile = $cacheDir . '/' . $cacheKey . '.json';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTtl)) {
        header('X-Cache: HIT');
        header('Cache-Control: public, max-age=' . $cacheTtl);
        echo file_get_contents($cacheFile);
        exit;
    }

    $stmt = $bdd->prepare($sql);
    $stmt->execute($params);

    $rows = $stmt->fetchAll(PDO::FETCH_NUM);

    // initialize grid cells
    $cells = [];
    for ($i = 0; $i < $gridSize; $i++) {
        $cells[$i] = [];
        for ($j = 0; $j < $gridSize; $j++) {
            $cells[$i][$j] = [];
        }
    }

    // bucket rows into cells
    foreach ($rows as $row) {
        $lat = (float)$row[1];
        $lon = (float)$row[2];

        $ii = $latStep > 0 ? intval(($lat - $minLat) / $latStep) : 0;
        $jj = $lonStep > 0 ? intval(($lon - $minLon) / $lonStep) : 0;

        if ($ii < 0) $ii = 0; elseif ($ii >= $gridSize) $ii = $gridSize - 1;
        if ($jj < 0) $jj = 0; elseif ($jj >= $gridSize) $jj = $gridSize - 1;

        $cells[$ii][$jj][] = [
            'id' => $row[0],
            'latitude' => $lat,
            'longitude' => $lon
        ];
    }

    // sample per cell
    for ($i = 0; $i < $gridSize; $i++) {
        for ($j = 0; $j < $gridSize; $j++) {
            $cellItems = $cells[$i][$j];
            $count = count($cellItems);
            if ($count === 0) continue;

            if ($count <= $pointsPerCell) {
                foreach ($cellItems as $item) $markers[] = $item;
            } else {
                $keys = array_rand($cellItems, $pointsPerCell);
                if (!is_array($keys)) $keys = [$keys];
                foreach ($keys as $k) $markers[] = $cellItems[$k];
            }
        }
    }
    
    shuffle($markers);

    $response = json_encode(['success' => true, 'markers' => $markers], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // store cache atomically
    if (!empty($cacheFile)) {
        $tmpFile = $cacheFile . '.' . uniqid('tmp', true);
        @file_put_contents($tmpFile, $response);
        @rename($tmpFile, $cacheFile);
    }

    header('X-Cache: MISS');
    header('Cache-Control: public, max-age=' . $cacheTtl);
    echo $response;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

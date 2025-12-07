<?php
require_once "bdd/connexion_bdd.php";

echo "<h1>Ajout des index pour performance</h1>";
echo "<pre>";

try {
    echo "=== INDEX ACTUELS ===\n";
    $r = $bdd->query('SHOW INDEX FROM equipements_sportifs');
    $indexes = $r->fetchAll(PDO::FETCH_ASSOC);
    foreach ($indexes as $idx) {
        echo "- {$idx['Key_name']} sur {$idx['Column_name']}\n";
    }
    
    $hasCoordIndex = false;
    foreach ($indexes as $idx) {
        if ($idx['Key_name'] === 'idx_coordonnees') {
            $hasCoordIndex = true;
            break;
        }
    }
    
    if (!$hasCoordIndex) {
        echo "\n=== AJOUT INDEX COORDONNEES ===\n";
        echo "Création en cours (peut prendre quelques secondes)...\n";
        $bdd->exec("CREATE INDEX idx_coordonnees ON equipements_sportifs (coordonnees_y, coordonnees_x)");
        echo "✓ Index idx_coordonnees créé!\n";
    } else {
        echo "\n✓ Index sur coordonnées déjà présent\n";
    }
    
    echo "\n=== TEST PERFORMANCE ===\n";
    
    $start = microtime(true);
    $stmt = $bdd->query("SELECT id, coordonnees_y, coordonnees_x FROM equipements_sportifs 
                         WHERE coordonnees_y IS NOT NULL AND coordonnees_x IS NOT NULL 
                         ORDER BY (FLOOR(coordonnees_y) + FLOOR(coordonnees_x)) % 100
                         LIMIT 300");
    $markers = $stmt->fetchAll();
    $time = round((microtime(true) - $start) * 1000);
    echo "300 markers: {$time}ms " . ($time < 500 ? "✓ OK" : "⚠ LENT") . "\n";
    
    $testId = $markers[0]['id'] ?? null;
    if ($testId) {
        $start = microtime(true);
        $stmt = $bdd->prepare("SELECT id, nom, type_equipement, commune, code_postal,
                               coordonnees_y, coordonnees_x, proprietaire_principal_type, 
                               sanitaires, acces_handi_mobilite, creation_dt, nb_remplie, nb_capacite 
                               FROM equipements_sportifs WHERE id = ? LIMIT 1");
        $stmt->execute([$testId]);
        $result = $stmt->fetch();
        $time = round((microtime(true) - $start) * 1000);
        echo "Détails par ID: {$time}ms " . ($time < 500 ? "✓ OK" : "⚠ LENT") . "\n";
    }
    
    echo "\n✓ Optimisation terminée!\n";
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}

echo "</pre>";

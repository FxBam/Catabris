<?php
$host = 'localhost';
$db   = 'test';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

set_time_limit(0);
ini_set('memory_limit', '1024M');

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

$pdo = new PDO($dsn, $user, $pass, $options);

$baseUrl = "https://equipements.sports.gouv.fr/api/explore/v2.1/records/json/?dataset=data-es&rows=1000&start=0";

$start = 0;
$totalInserts = 0;

while (true) {
    echo "Récupération des enregistrements à partir de $start...\n";

    $json = file_get_contents($baseUrl . $start);
    if (!$json) break;

    $data = json_decode($json, true);
    if (!isset($data['records']) || count($data['records']) === 0) {
        echo "Fin des données.\n";
        break;
    }

    foreach ($data['records'] as $record) {
        $fields = $record['fields'];
        $id = $record['recordid'];

        // Vérification doublon
        $check = $pdo->prepare("SELECT id FROM equipements_sportifs WHERE id = ?");
        $check->execute([$id]);
        if ($check->rowCount() > 0) continue;

        $stmt = $pdo->prepare("
            INSERT INTO equipements_sportifs
            (id, uai, nom, type_equipement, proprietaire, siret,
            longueur, largeur, surface, type_sol, nature_equipement,
            adresse, commune, latitude, longitude,
            mail, telephone, site_web, accessibilite, observations)
            VALUES
            (:id, :uai, :nom, :type_equipement, :proprietaire, :siret,
            :longueur, :largeur, :surface, :type_sol, :nature_equipement,
            :adresse, :commune, :latitude, :longitude,
            :mail, :telephone, :site_web, :accessibilite, :observations)
        ");

        $stmt->execute([
            ':id' => $id,
            ':uai' => $fields['inst_uai'] ?? null,
            ':nom' => $fields['inst_nom'] ?? null,
            ':type_equipement' => $fields['equip_type_name'] ?? null,
            ':proprietaire' => $fields['equip_prop_nom'] ?? null,
            ':siret' => $fields['inst_siret'] ?? null,

            ':longueur' => $fields['equip_long'] ?? null,
            ':largeur' => $fields['equip_larg'] ?? null,
            ':surface' => $fields['equip_surf'] ?? null,
            ':type_sol' => $fields['equip_sol'] ?? null,
            ':nature_equipement' => $fields['equip_nature'] ?? null,

            ':adresse' => $fields['inst_adresse'] ?? null,
            ':commune' => $fields['new_name'] ?? null,
            ':latitude' => $fields['equip_y'] ?? null,
            ':longitude' => $fields['equip_x'] ?? null,

            ':mail' => $fields['mail'] ?? null,
            ':telephone' => $fields['telephone'] ?? null,
            ':site_web' => $fields['site_web'] ?? null,
            ':accessibilite' => $fields['equip_acces_handi_mobilite'] ?? null,
            ':observations' => $fields['equip_obs'] ?? null
        ]);

        $totalInserts++;
    }

    $start += 1000; // page suivante
}

echo "\nImport terminé ! Total d'enregistrements ajoutés : $totalInserts\n";
?>

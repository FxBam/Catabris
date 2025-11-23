<?php
require_once "../bdd/connexion_bdd.php";
?>

<?php
$sql = "SELECT latitude, longitude, nom FROM equipements_sportifs";
$stmt = $bdd->prepare($sql);
$stmt->execute();

$points = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $points[] = [
        'latitude'  => (float)$row['latitude'],
        'longitude' => (float)$row['longitude'],
        'nom'       => $row['nom']
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <title>Catabris</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    </head>
    <body>
        <div id="navBar"></div>
        <div class="container">
            <div id="controlPanel" class="controlPanel"></div>
            <main>
                <h1>Bienvenue sur Catabris</h1>
                <h3>Utiliser la carte pour localiser l'equipement sportif de votre choix</h3>
                <div class="mapouter">
                    <div class="osm_canvas">
                        <iframe id="osm_canvas" src="about:blank" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>
                        <script>
                            function resizeIframe() {
                                const container = document.querySelector('.osm_canvas');
                                const width = container.clientWidth;
                                const height = container.clientHeight - 100;

                                const iframeDoc = document.getElementById('osm_canvas').contentDocument;
                                iframeDoc.open();
                                iframeDoc.write(`
                                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.3/dist/leaflet.min.css" />
                                    <div id="osm-map" style="width:${width}px; height:${height}px;"></div>
                                    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.3/dist/leaflet.min.js"><\/script>
                                    <script>
                                        var map = L.map("osm-map").setView([48.8566, 2.3522], 12);
                                        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                                            maxZoom: 19,
                                            attribution: '&copy; OpenStreetMap contributors'
                                        }).addTo(map);

                                        var lieux = ${JSON.stringify(<?php echo json_encode($points); ?>)};
                                        lieux.forEach(function(lieu) {
                                            L.marker([lieu.latitude, lieu.longitude])
                                            .addTo(map)
                                            .bindPopup(lieu.nom);
                                        });
                                    <\/script>
                                `);
                                iframeDoc.close();
                            }
                            
                            window.addEventListener('load', resizeIframe);
                            window.addEventListener('resize', resizeIframe);

                            
                        </script>
                    </div>
                </div> 
            </main>
        </div>
        <script>
            $(function() {
                $("#navBar").load("navBar.php");
                $("#controlPanel").load("controlPanel.php");
            });
        </script>
    </body>
</html>
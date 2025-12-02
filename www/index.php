<?php
require_once "../bdd/connexion_bdd.php";
session_start();

$page = $_SERVER['REQUEST_URI'];
$ip = $_SERVER['REMOTE_ADDR'];

$stmt = $bdd->prepare("INSERT INTO vues_site (page, ip) VALUES (:page, :ip)");
$stmt->execute([
    ':page' => $page,
    ':ip' => $ip
]);

$sql = $sql = "SELECT 
            coordonnees_y AS latitude,
            coordonnees_x AS longitude,
            nom,
            proprietaire_principal_nom AS proprietaire,
            type_equipement,
            acces_libre AS accessibilite,
            commune,
            website AS site_web
        FROM equipements_sportifs 
        WHERE coordonnees_y IS NOT NULL 
          AND coordonnees_x IS NOT NULL
        ORDER BY nom
        LIMIT 500";
$stmt = $bdd->prepare($sql);
$stmt->execute();

$points = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $points[] = [
        'latitude'        => (float) $row['latitude'],
        'longitude'       => (float) $row['longitude'],
        'nom'             => (string) $row['nom'],
        'proprietaire'    => (string) $row['proprietaire'],
        'type_equipement' => (string) $row['type_equipement'],
        'accessibilite'   => (string) $row['accessibilite'],
        'commune'         => (string) $row['commune'],
        'site_web'        => (string) $row['site_web']
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
                <div class="mapouter">
                    <div class="osm_canvas">
                        <iframe id="osm_canvas" src="about:blank" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>
                        <script>
                            function resizeIframe() {
                                const container = document.querySelector('.osm_canvas');
                                const width = container.clientWidth;
                                const height = container.clientHeight;

                                const iframe = document.getElementById('osm_canvas');
                                iframe.style.width = width + 'px';
                                iframe.style.height = height + 'px';

                                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

                                iframeDoc.open();
                                iframeDoc.write(`
                                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.3/dist/leaflet.css" />
                                    <style>
                                        #osm-map { width: ${width}px; height: ${height}px; }
                                        
                                        #bottom-panel {
                                            position: fixed;
                                            bottom: -300px;
                                            left: 2%;
                                            width: 80%;
                                            max-width: 300px;
                                            max-height: 600px;
                                            background-color: white;
                                            box-shadow: 0 -4px 10px rgba(0,0,0,0.2);
                                            border-top-left-radius: 10px;
                                            border-top-right-radius: 10px;
                                            padding: 20px;
                                            transition: bottom 0.3s ease-in-out;
                                            overflow-y: auto;
                                            z-index: 1000;
                                        }

                                        #bottom-panel.active { bottom: 0; }

                                        #close-btn {
                                            position: absolute;
                                            top: 10px;
                                            right: 20px;
                                            font-size: 24px;
                                            cursor: pointer;
                                        }
                                    </style>

                                    <div id="osm-map"></div>
                                    <div id="bottom-panel">
                                        <span id="close-btn">&times;</span>
                                        <div id="panel-content"></div>
                                    </div>

                                    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.3/dist/leaflet.js"><\/script>
                                    <script>
                                        var map = L.map("osm-map").setView([48.8566, 2.3522], 12);
                                        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                                            maxZoom: 19,
                                            attribution: '&copy; OpenStreetMap contributors'
                                        }).addTo(map);

                                        var lieux = ${JSON.stringify(<?php echo json_encode($points); ?>)};

                                        lieux.forEach(function(lieu) {
                                            var marker = L.marker([lieu.latitude, lieu.longitude]).addTo(map);
                                            marker.on('click', function() {
                                                // Centrer la carte sur le marqueur sélectionné
                                                map.setView([lieu.latitude, lieu.longitude], 15);
                                                
                                                var content = '<h2>' + lieu.nom + '</h2><p>';
                                                
                                                if (lieu.proprietaire && lieu.proprietaire.trim() !== '') {
                                                    content += 'Proprietaire : ' + lieu.proprietaire + '<br>';
                                                }
                                                if (lieu.type_equipement && lieu.type_equipement.trim() !== '') {
                                                    content += 'Type d équipement : ' + lieu.type_equipement + '<br>';
                                                }
                                                if (lieu.accessibilite && lieu.accessibilite.trim() !== '') {
                                                    content += 'Accessibilite : ' + lieu.accessibilite + '<br>';
                                                }
                                                if (lieu.commune && lieu.commune.trim() !== '') {
                                                    content += 'Commune : ' + lieu.commune + '<br>';
                                                }
                                                if (lieu.site_web && lieu.site_web.trim() !== '') {
                                                    if ('http' !== lieu.site_web.substring(0, 4) && 'https' !== lieu.site_web.substring(0, 5)) {
                                                        lieu.site_web = 'http://' + lieu.site_web;
                                                    }
                                                    content += 'Site web : ' + '<a href="' + lieu.site_web + '" target="_blank">' + 'Site web du lieu</a>';
                                                }
                                                
                                                content += '</p>';
                                                document.getElementById('panel-content').innerHTML = content;
                                                document.getElementById('bottom-panel').classList.add('active');
                                            });
                                        });

                                        document.getElementById('close-btn').addEventListener('click', function() {
                                            document.getElementById('bottom-panel').classList.remove('active');
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
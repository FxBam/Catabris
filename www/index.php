<?php
require_once "../bdd/connexion_bdd.php";
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
                                
                                document.getElementById('osm_canvas').contentDocument.write('<link rel = "stylesheet" href = "https://cdn.jsdelivr.net/npm/leaflet@1.9.3/dist/leaflet.min.css" />');
                                document.getElementById('osm_canvas').contentDocument.write('<script src = "https://cdn.jsdelivr.net/npm/leaflet@1.9.3/dist/leaflet.min.js"><\/script>');
                                document.getElementById('osm_canvas').contentDocument.write('<div id ="osm-map" style = "width:' + width + 'px; height:' + height + 'px;"></div>');
                                document.getElementById('osm_canvas').contentDocument.write('<script>var map = L.map("osm-map").setView([48.8566, 2.3522], 12);L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {maxZoom: 19,attribution: \'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors\'}).addTo(map);<\/script>');
                                document.getElementById('osm_canvas').contentDocument.close();
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
<?php
require_once "../bdd/connexion_bdd.php";
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='0.9em' font-size='90'>🏟️</text></svg>">
        <link rel="stylesheet" href="style/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <title>Contact</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
        <style>
            .api-table { width:100%; border-collapse:collapse; margin-bottom:18px; }
            .api-table td { border:1px solid #e0e0e0; padding:6px 10px; }
            .api-examples pre, .api-json pre {
                margin: 0 0 12px 0;
                padding: 0;
                background: #f8f8f8;
                border-radius: 4px;
                overflow-x: auto;
                text-align: left;
            }
            .api-examples pre code, .api-json pre code {
                display: block;
                padding: 0;
                margin: 0;
                background: none;
                font-size: 1em;
                white-space: pre;
                text-align: left;
            }
        </style>
    </head>
    <body class="contact-page">
        <div id="navBar"></div> 
        <div class="container">
            <div id="controlPanel" class="controlPanel"></div>
            <main>
                <h1>API Catabris - Documentation</h1>
                <div class="info-section">
                    <h2>Liste des équipements avec pagination</h2>
                    <h3>Paramètres</h3>
                    <table class="api-table">
                        <tbody>
                            <tr><td><code>page</code> (optionnel)</td><td>Numéro de page (défaut: 1)</td></tr>
                            <tr><td><code>limit</code> (optionnel)</td><td>Nombre d'équipements par page (défaut: 20, max: 100)</td></tr>
                            <tr><td><code>q</code> (optionnel)</td><td>Recherche par nom ou commune</td></tr>
                            <tr><td><code>id</code> (optionnel)</td><td>Récupérer un équipement spécifique</td></tr>
                            <tr><td><code>minLat, maxLat, minLon, maxLon</code> (optionnel)</td><td>Filtrage géographique</td></tr>
                        </tbody>
                    </table>
                    <h3>Exemples d'utilisation</h3>
                    <div class="api-examples">
                        <pre><code>
                            # Page 1 avec 20 équipements
                            http://localhost/api/equipements.php?page=1&limit=20

                            # Recherche avec pagination
                            http://localhost/api/equipements.php?page=1&limit=10&q=Paris

                            # Filtrage géographique
                            http://localhost/api/equipements.php?minLat=48.8&maxLat=48.9&minLon=2.3&maxLon=2.4

                            # Récupérer un équipement spécifique
                            http://localhost/api/equipements.php?id=123</code></pre>
                    </div>
                    <h3>Réponse JSON</h3>
                    <div class="api-json">
                        <pre><code>
                            "success": true,
                            "page": 1,
                            "limit": 20,
                            "total_count": 150,
                            "total_pages": 8,
                            "count": 20,
                            "data": [
                                {
                                    "id": "1",
                                    "nom": "Stade Municipal",
                                    "type_equipement": "Stade",
                                    "commune": "Paris",
                                    "code_postal": "75001",
                                    "adresse": "1 rue du Sport",
                                    "latitude": 48.8566,
                                    "longitude": 2.3522,
                                    "proprietaire_principal_type": "Commune",
                                    "sanitaires": true,
                                    "acces_handi_mobilite": true,
                                    "creation_dt": "2024-01-01 10:00:00",
                                    "maj_date": "2024-12-01 15:30:00"
                                }
                            ]
                        </code></pre>
                    </div>
                    <hr>
                    <h2>Suggestions (Autocomplete)</h2>
                    <p><strong>URL</strong> : <code>/api/suggestions.php</code> — <strong>Méthode</strong> : GET</p>
                    <h3>Paramètres</h3>
                    <table class="api-table">
                        <tbody>
                            <tr><td><code>q</code> (requis)</td><td>Terme de recherche</td></tr>
                            <tr><td><code>limit</code> (optionnel)</td><td>Nombre de suggestions (défaut: 10, max: 50)</td></tr>
                            <tr><td><code>minLat, maxLat, minLon, maxLon</code> (optionnel)</td><td>Prioriser par position</td></tr>
                        </tbody>
                    </table>
                    <h3>Exemples d'utilisation</h3>
                    <div class="api-examples">
                        <pre><code>
                            # Recherche simple
                            http://localhost/api/suggestions.php?q=stade

                            # Avec limite personnalisée
                            http://localhost/api/suggestions.php?q=piscine&limit=5

                            # Prioriser par position géographique
                            http://localhost/api/suggestions.php?q=terrain&minLat=48.8&maxLat=48.9&minLon=2.3&maxLon=2.4</code></pre>
                    </div>
                    <h3>Réponse JSON</h3>
                    <div class="api-json">
                        <pre><code>
                            "success": true,
                            "count": 5,
                            "suggestions": [
                                {
                                    "id": "1",
                                    "label": "Stade Municipal - Paris",
                                    "nom": "Stade Municipal",
                                    "commune": "Paris",
                                    "type": "Stade",
                                    "lat": 48.8566,
                                    "lon": 2.3522
                                }
                            ]
                        </code></pre>
                    </div>
                    <hr>
                    <h2>Documentation de l'API</h2>
                    <p><strong>URL</strong> : <code>/api/index.php</code> — <strong>Méthode</strong> : GET<br>Retourne la documentation complète de l'API en JSON.</p>
                    <hr>
                    <h2>Notes importantes</h2>
                    <ul>
                        <li><strong>Performance</strong> : La pagination limite le nombre d'équipements retournés, réduisant la charge serveur</li>
                        <li><strong>Cache</strong> : Envisagez d'ajouter un système de cache pour les requêtes fréquentes</li>
                        <li><strong>Sécurité</strong> : Tous les paramètres sont validés et échappés pour éviter les injections SQL</li>
                        <li><strong>CORS</strong> : L'API accepte les requêtes cross-origin (<code>Access-Control-Allow-Origin: *</code>)</li>
                    </ul>
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

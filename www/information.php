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
    </head>
    <body class="contact-page">
        <div id="navBar"></div> 
        <div class="container">
            <div id="controlPanel" class="controlPanel"></div>
            <main>
                <h1>Informations</h1>
                <div class="info-section">

                    <h2>But du projet</h2>
                    <p>L’objectif de cette application est de permettre d'avoir accèes à toutes les informations concernant les équipements sportifs existants en France, ainsi que localiser le point de sureté le plus proche en cas de catastrophe naturelle.</p>
                    <p>Elle permet :</p>
                    <ul>
                        <li>Pour les collectivités (locales, territoriales, communautés de communes, région …) de saisir les équipements disponibles (géolocalisation, type d’équipement, dimension, tribunes, aménagements, accessibilité, utilisateurs, etc.) ainsi que les éléments liés à son installation (adresse, propriétaire, ERP, type d’établissement …).</li>
                        <li>Pour les utilisateurs potentiels de récupérer de la donnée liée aux équipements sportifs.</li>
                    </ul>

                    <h3>Exemples d’utilisation :</h3>
                    <ul>
                        <li>Collectivités souhaitant mettre à disposition le jeu de données sur les équipements sportifs de leur territoire ou le ministère cherchant des gymnases couverts disponibles et géolocalisés en cas de catastrophe naturelle.</li>
                        <li>Startup proposant du matchmaking sur des parties de Basket 3x3 cherchant les terrains de basket disponibles en accès libre autour d’une position GPS.</li>
                    </ul>

                    <h2>Données disponibles</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Types de caractéristiques</th>
                                <th>Exemples</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Caractéristiques administratives</td>
                                <td>Type de propriétaire, SIRET, UAI (si établissement scolaire)</td>
                            </tr>
                            <tr>
                                <td>Caractéristiques structurelles</td>
                                <td>Longueur, largeur, surface, type de sol, nature de l'équipement</td>
                            </tr>
                            <tr>
                                <td>Caractéristiques géographiques</td>
                                <td>Adresse, commune, coordonnées GPS</td>
                            </tr>
                            <tr>
                                <td>Autres caractéristiques</td>
                                <td>URL, observations, accessibilité</td>
                             </tr>
                        </tbody>
                    </table>

                    <h2>API suggérée</h2>
                    <p>Vous pouvez utiliser les données via l’API suivante :</p>
                    <p><a href="https://api.gouv.fr/documentation/api-data-es" target="_blank">https://api.gouv.fr/documentation/api-data-es</a></p>
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

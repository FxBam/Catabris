<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$documentation = [
    'api_version' => '1.0',
    'endpoints' => [
        [
            'path' => '/api/equipements.php',
            'method' => 'GET',
            'description' => 'Récupère la liste des équipements sportifs avec pagination',
            'parameters' => [
                'page' => 'Numéro de page (défaut: 1)',
                'limit' => 'Nombre d\'équipements par page (défaut: 20, max: 100)',
                'q' => 'Recherche par nom ou commune (optionnel)',
                'id' => 'Récupérer un équipement spécifique (optionnel)',
                'minLat' => 'Latitude minimum pour filtrage géographique (optionnel)',
                'maxLat' => 'Latitude maximum pour filtrage géographique (optionnel)',
                'minLon' => 'Longitude minimum pour filtrage géographique (optionnel)',
                'maxLon' => 'Longitude maximum pour filtrage géographique (optionnel)'
            ],
            'example' => '/api/equipements.php?page=1&limit=20&q=Paris'
        ],
        [
            'path' => '/api/suggestions.php',
            'method' => 'GET',
            'description' => 'Récupère des suggestions pour l\'autocomplete',
            'parameters' => [
                'q' => 'Terme de recherche (requis)',
                'limit' => 'Nombre de suggestions (défaut: 10, max: 50)',
                'minLat' => 'Latitude minimum pour prioriser résultats (optionnel)',
                'maxLat' => 'Latitude maximum pour prioriser résultats (optionnel)',
                'minLon' => 'Longitude minimum pour prioriser résultats (optionnel)',
                'maxLon' => 'Longitude maximum pour prioriser résultats (optionnel)'
            ],
            'example' => '/api/suggestions.php?q=stade'
        ]
    ],
    'response_format' => [
        'success' => 'Boolean indiquant le succès de la requête',
        'page' => 'Numéro de page actuel (pagination)',
        'limit' => 'Nombre d\'éléments par page',
        'total_count' => 'Nombre total d\'équipements',
        'total_pages' => 'Nombre total de pages',
        'count' => 'Nombre d\'éléments dans la réponse',
        'data' => 'Tableau des équipements/suggestions'
    ]
];

echo json_encode($documentation, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>

<?php
require_once "../bdd/connexion_bdd.php";
session_start();

$page = $_SERVER['REQUEST_URI'];
$ip = $_SERVER['REMOTE_ADDR'];
$stmt = $bdd->prepare("INSERT INTO vues_site (page, ip) VALUES (:page, :ip)");
$stmt->execute([':page' => $page, ':ip' => $ip]);

$query = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='0.9em' font-size='90'>🏟️</text></svg>">
        <link rel="stylesheet" href="style/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.3/dist/leaflet.css" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.3.0/dist/MarkerCluster.css" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.3.0/dist/MarkerCluster.Default.css" />
        <title>Catabris</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.3/dist/leaflet.js"></script>
        <script src="https://unpkg.com/leaflet.markercluster@1.3.0/dist/leaflet.markercluster.js"></script>
    </head>
    <body>
        <div id="navBar"></div>
        <div class="container">
            <div id="controlPanel" class="controlPanel"></div>
            <main>
                <div id="map-container">
                    <div id="floating-panel">
                        <div id="search-container">
                            <input type="text" id="search-input" placeholder="Rechercher un équipement, ville..." value="<?= $query ?>">
                        </div>
                        <div id="suggestions-list"></div>
                    </div>
                    
                    <div id="loading-indicator">
                        <i class="fas fa-spinner fa-spin"></i> Chargement...
                    </div>
                    
                    <div id="map"></div>
                    
                    <div id="equipement-panel">
                        <div class="panel-header">
                            <h3 id="panel-title">Équipement</h3>
                            <span class="close-panel" id="close-panel">&times;</span>
                        </div>
                        <div class="panel-body" id="panel-body"></div>
                    </div>
                </div>
            </main>
        </div>
        
        <script>
            const API_BASE = '/Catabris/api';
            
            const map = L.map('map').setView([46.603354, 1.888334], 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);
            
            const markers = L.markerClusterGroup({
                chunkedLoading: true,
                chunkInterval: 50,
                chunkDelay: 10,
                maxClusterRadius: 60,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                disableClusteringAtZoom: 18,
                removeOutsideVisibleBounds: true
            });
            map.addLayer(markers);
            
            let currentBounds = null;
            let loadTimeout = null;
            let searchTimeout = null;
            
            function showLoading(show) {
                document.getElementById('loading-indicator').style.display = show ? 'block' : 'none';
            }
            
            async function loadEquipements(query = '') {
                const bounds = map.getBounds();
                const params = new URLSearchParams({
                    limit: 4000,
                    minLat: bounds.getSouth(),
                    maxLat: bounds.getNorth(),
                    minLon: bounds.getWest(),
                    maxLon: bounds.getEast()
                });
                
                if (query) {
                    params.append('q', query);
                }
                
                const newBounds = {
                    minLat: bounds.getSouth(),
                    maxLat: bounds.getNorth(),
                    minLon: bounds.getWest(),
                    maxLon: bounds.getEast()
                };
                
                if (currentBounds && !query &&
                    Math.abs(currentBounds.minLat - newBounds.minLat) < 0.05 &&
                    Math.abs(currentBounds.maxLat - newBounds.maxLat) < 0.05 &&
                    Math.abs(currentBounds.minLon - newBounds.minLon) < 0.05 &&
                    Math.abs(currentBounds.maxLon - newBounds.maxLon) < 0.05) {
                    return;
                }
                
                currentBounds = newBounds;
                showLoading(true);
                
                try {
                    const response = await fetch(`${API_BASE}/markers.php?${params}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        markers.clearLayers();
                        
                        data.markers.forEach(marker => {
                            if (marker.latitude && marker.longitude) {
                                const leafletMarker = L.marker([marker.latitude, marker.longitude]);
                                leafletMarker.equipementId = marker.id;
                                
                                leafletMarker.on('click', async function() {
                                    await loadEquipementDetails(this.equipementId);
                                });
                                
                                markers.addLayer(leafletMarker);
                            }
                        });
                        
                        console.log(`${data.markers.length} marqueurs chargés`);
                    }
                } catch (error) {
                    console.error('Erreur chargement marqueurs:', error);
                } finally {
                    showLoading(false);
                }
            }
            
            async function loadEquipementDetails(equipId) {
                showLoading(true);
                
                try {
                    const response = await fetch(`${API_BASE}/equipements.php?id=${equipId}`);
                    const data = await response.json();
                    
                    if (data.success && data.data && data.data.length > 0) {
                        const equip = data.data[0];
                        showEquipementPanel(equip);
                        map.setView([equip.latitude, equip.longitude], 15);
                    } else {
                        console.error('Équipement non trouvé');
                    }
                } catch (error) {
                    console.error('Erreur chargement détails:', error);
                } finally {
                    showLoading(false);
                }
            }
            
            function createCapacityGauge(nbRemplie, nbCapacite) {
                if (!nbCapacite || nbCapacite <= 0) return '';
                
                const percentage = Math.min((nbRemplie / nbCapacite) * 100, 100);
                const radius = 48;
                const circumference = 2 * Math.PI * radius;
                const dashOffset = circumference - (percentage / 100) * circumference;
                
                let colorClass = 'gauge-green';
                if (percentage >= 75) colorClass = 'gauge-red';
                else if (percentage >= 50) colorClass = 'gauge-orange';
                else if (percentage >= 25) colorClass = 'gauge-yellow';
                
                return `
                    <div class="capacity-gauge">
                        <div class="gauge-container">
                            <svg class="gauge-svg" viewBox="0 0 120 120">
                                <circle class="gauge-background" cx="60" cy="60" r="${radius}" 
                                    stroke-dasharray="${circumference}" stroke-dashoffset="0"/>
                                <circle class="gauge-fill ${colorClass}" cx="60" cy="60" r="${radius}" 
                                    stroke-dasharray="${circumference}" stroke-dashoffset="${dashOffset}"/>
                            </svg>
                            <span class="gauge-text">${nbRemplie}/${nbCapacite}</span>
                        </div>
                        <span class="gauge-label">Taux de remplissage: ${Math.round(percentage)}%</span>
                    </div>
                `;
            }
            
            async function incrementerRemplissage(equipId) {
                try {
                    const response = await fetch(`${API_BASE}/incrementer.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: equipId })
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        await loadEquipementDetails(equipId);
                    }
                } catch (error) {
                    console.error('Erreur incrémentation:', error);
                }
            }
            
            function showEquipementPanel(equip) {
                document.getElementById('panel-title').textContent = equip.nom || 'Équipement';
                
                let html = '';
                
                html += '<div style="margin-bottom: 10px;">';
                if (equip.type_equipement) {
                    html += `<span class="badge-info">${equip.type_equipement}</span>`;
                }
                if (equip.sanitaires && equip.sanitaires === 'Oui') {
                    html += `<span class="badge-info"><i class="fas fa-restroom"></i> Sanitaires</span>`;
                }
                if (equip.acces_handi_mobilite && equip.acces_handi_mobilite !== '' && equip.acces_handi_mobilite !== null) {
                    html += `<span class="badge-info"><i class="fas fa-wheelchair"></i> PMR</span>`;
                }
                if (equip.acces_handi_sensoriel && equip.acces_handi_sensoriel !== '' && equip.acces_handi_sensoriel !== null) {
                    html += `<span class="badge-info"><i class="fas fa-ear-deaf"></i> Sensoriel</span>`;
                }
                html += '</div>';
                
                if (equip.nb_capacite && equip.nb_capacite > 0) {
                    html += createCapacityGauge(equip.nb_remplie || 0, equip.nb_capacite);
                }
                
                if (equip.commune) {
                    html += `<div class="info-row"><i class="fas fa-map-marker-alt"></i><span>${equip.commune}</span></div>`;
                }
                
                if (equip.proprietaire_principal_type) {
                    html += `<div class="info-row"><i class="fas fa-building"></i><span>Propriétaire: ${equip.proprietaire_principal_type}</span></div>`;
                }
                
                if (equip.creation_dt) {
                    const date = new Date(equip.creation_dt);
                    html += `<div class="info-row"><i class="fas fa-calendar"></i><span>Créé le: ${date.toLocaleDateString('fr-FR')}</span></div>`;
                }
                
                if (equip.latitude && equip.longitude) {
                    html += `<a href="https://www.google.com/maps/dir/?api=1&destination=${equip.latitude},${equip.longitude}" 
                              target="_blank" 
                              class="btn-itinerary" 
                              onclick="incrementerRemplissage('${equip.id}');">
                              <i class="fas fa-directions"></i> Itinéraire
                            </a>`;
                }
                
                document.getElementById('panel-body').innerHTML = html;
                document.getElementById('equipement-panel').classList.add('active');
            }
            
            async function loadSuggestions(query) {
                if (!query || query.length < 2) {
                    document.getElementById('suggestions-list').style.display = 'none';
                    return;
                }
                
                try {
                    const response = await fetch(`${API_BASE}/suggestions.php?q=${encodeURIComponent(query)}&limit=10`);
                    const data = await response.json();
                    
                    if (data.success && data.suggestions.length > 0) {
                        const list = document.getElementById('suggestions-list');
                        list.innerHTML = data.suggestions.map(s => `
                            <div class="suggestion-item" data-lat="${s.lat}" data-lon="${s.lon}" data-id="${s.id}">
                                <div class="suggestion-name">${s.nom}</div>
                                <div class="suggestion-commune">${s.commune || ''} ${s.type ? '- ' + s.type : ''}</div>
                            </div>
                        `).join('');
                        list.style.display = 'block';
                        
                        list.querySelectorAll('.suggestion-item').forEach(item => {
                            item.addEventListener('click', function() {
                                const lat = parseFloat(this.dataset.lat);
                                const lon = parseFloat(this.dataset.lon);
                                
                                if (lat && lon) {
                                    map.setView([lat, lon], 15);
                                    currentBounds = null;
                                    loadEquipements();
                                }
                                
                                document.getElementById('suggestions-list').style.display = 'none';
                                document.getElementById('search-input').value = this.querySelector('.suggestion-name').textContent;
                            });
                        });
                    } else {
                        document.getElementById('suggestions-list').style.display = 'none';
                    }
                } catch (error) {
                    console.error('Erreur chargement suggestions:', error);
                }
            }
            
            document.getElementById('close-panel').addEventListener('click', function() {
                document.getElementById('equipement-panel').classList.remove('active');
            });
            
            document.getElementById('search-input').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => loadSuggestions(this.value), 300);
            });
            
            document.getElementById('search-input').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    document.getElementById('suggestions-list').style.display = 'none';
                    currentBounds = null;
                    loadEquipements(this.value);
                }
            });
            
            document.addEventListener('click', function(e) {
                if (!e.target.closest('#floating-panel')) {
                    document.getElementById('suggestions-list').style.display = 'none';
                }
            });
            
            map.on('moveend', function() {
                clearTimeout(loadTimeout);
                loadTimeout = setTimeout(() => loadEquipements(), 500);
            });
            
            map.whenReady(function() {
                const initialQuery = '<?= $query ?>';
                if (initialQuery) {
                    loadEquipements(initialQuery);
                } else {
                    loadEquipements();
                }
            });
            
            $(function() {
                $("#navBar").load("navBar.php");
                $("#controlPanel").load("controlPanel.php");
            });
        </script>
    </body>
</html>
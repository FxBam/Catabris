<?php
require_once "../bdd/connexion_bdd.php";
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Mes favoris</title>
</head>
<body class="favoris-page">
    <div id="navBar"></div>
    <div class="container">
        <div id="controlPanel" class="controlPanel"></div>
        <main>
            <h1>Mes favoris</h1>
            <div class="card">
                <div id="favoris-container">
                    <p>Chargement des favoris...</p>
                </div>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const API_BASE = '/Catabris/api';

        $(function() {
            $("#navBar").load("navBar.php");
            $("#controlPanel").load("controlPanel.php");
            loadFavoris();
        });

        async function loadFavoris() {
            const container = document.getElementById('favoris-container');
            container.innerHTML = '<p>Chargement des favoris...</p>';

            try {
                const res = await fetch(`${API_BASE}/favoris.php`, { credentials: 'same-origin' });
                const data = await res.json();

                if (!data.success) {
                    container.innerHTML = '<p>Impossible de récupérer les favoris.</p>';
                    return;
                }

                const favoris = data.favoris || [];
                if (favoris.length === 0) {
                    container.innerHTML = '<p>Aucun favori pour le moment.</p>';
                    return;
                }

                let html = '<table class="search-section"><thead><tr>' +
                    '<th>Nom</th><th>Type</th><th>Commune</th><th>Propriétaire</th><th>Sanitaires</th><th>PMR</th><th>Sensoriel</th><th>Actions</th>' +
                    '</tr></thead><tbody>';

                for (const id of favoris) {
                    try {
                        const r = await fetch(`${API_BASE}/equipements.php?id=${encodeURIComponent(id)}`);
                        const e = await r.json();
                        if (e.success && e.data && e.data.length > 0) {
                            const equip = e.data[0];
                            html += `<tr data-id="${equip.id}">` +
                                `<td>${escapeHtml(equip.nom || '')}</td>` +
                                `<td>${escapeHtml(equip.type_equipement || '')}</td>` +
                                `<td>${escapeHtml(equip.commune || '')}</td>` +
                                `<td>${escapeHtml(equip.proprietaire_principal_type || '')}</td>` +
                                `<td>${escapeHtml(equip.sanitaires || '')}</td>` +
                                `<td>${escapeHtml(equip.acces_handi_mobilite || '')}</td>` +
                                `<td>${escapeHtml(equip.acces_handi_sensoriel || '')}</td>` +
                                `<td>` +
                                    `<button class="btn-view" data-id="${equip.id}"><i class="fas fa-map-marker-alt"></i> Voir</button>` +
                                    ` <button class="btn-delete" data-id="${equip.id}"><i class="fas fa-trash"></i> Supprimer</button>` +
                                `</td>` +
                                `</tr>`;
                        }
                    } catch (err) {
                        console.error('Erreur chargement équipement', id, err);
                    }
                }

                html += '</tbody></table>';
                container.innerHTML = html;

                document.querySelectorAll('.btn-view').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.dataset.id;
                        window.location.href = `index.php?focus=${encodeURIComponent(id)}`;
                    });
                });

                document.querySelectorAll('.btn-delete').forEach(btn => {
                    btn.addEventListener('click', async function() {
                        const id = this.dataset.id;
                        if (!confirm('Supprimer cet établissement de vos favoris ?')) return;

                        try {
                            const resp = await fetch(`${API_BASE}/favoris.php`, {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ equipement_id: id, action: 'remove' })
                            });
                            const j = await resp.json();
                            if (j.success) {
                                await loadFavoris();
                            } else {
                                alert('Erreur suppression');
                            }
                        } catch (err) {
                            console.error(err);
                            alert('Erreur suppression');
                        }
                    });
                });

            } catch (err) {
                console.error(err);
                container.innerHTML = '<p>Erreur lors du chargement des favoris.</p>';
            }
        }

        function escapeHtml(unsafe) {
            return String(unsafe)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    </script>
</body>
</html>

-- Vérifier les index existants
SHOW INDEX FROM equipements_sportifs;

-- Ajouter des index pour améliorer les performances
-- Index sur la clé primaire (si pas déjà présent)
ALTER TABLE equipements_sportifs ADD PRIMARY KEY (id);

-- Index sur les coordonnées pour les requêtes spatiales
ALTER TABLE equipements_sportifs ADD INDEX idx_coordonnees (coordonnees_y, coordonnees_x);

-- Index sur nom et commune pour les recherches
ALTER TABLE equipements_sportifs ADD INDEX idx_nom (nom);
ALTER TABLE equipements_sportifs ADD INDEX idx_commune (commune);

-- Index composite pour les recherches par zone géographique
ALTER TABLE equipements_sportifs ADD INDEX idx_geo_bounds (coordonnees_y, coordonnees_x, id);

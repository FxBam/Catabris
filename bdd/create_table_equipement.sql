CREATE TABLE equipements_sportifs (
    id VARCHAR(50) PRIMARY KEY, --Id present dans le json de l'api
    
    uai VARCHAR(20),
    nom VARCHAR(255),
    type_equipement VARCHAR(100),
    proprietaire VARCHAR(255),
    siret VARCHAR(50),
    
    longueur DECIMAL(10,2),
    largeur DECIMAL(10,2),
    surface DECIMAL(10,2),
    type_sol VARCHAR(100),
    nature_equipement VARCHAR(100),
    
    adresse VARCHAR(255),
    commune VARCHAR(150),
    latitude DECIMAL(10,7),
    longitude DECIMAL(10,7),
    
    mail VARCHAR(255),
    telephone VARCHAR(50),
    site_web VARCHAR(255),
    accessibilite VARCHAR(255), --Données d'accessibilité PMR
    observations TEXT, --Données suplémentaires
    
    imported_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

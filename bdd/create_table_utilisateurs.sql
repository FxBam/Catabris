CREATE TABLE utilisateurs (
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    adresse VARCHAR(200) NOT NULL,
    code_postal VARCHAR(5) NOT NULL, --5 Char (Respecter les cinq 0 devant certains CP)
    adresse_mail VARCHAR(100) PRIMARY KEY, --Primary Key (Moyen de se connecter)
    mot_de_passe VARCHAR(255) NOT NULL, --MDP hasshé (255 caractères pour le hash)
    compte_admin BOOLEAN DEFAULT FALSE, --Validation admin
    favoris TEXT DEFAULT NULL --Liste des équipements favoris (JSON Array of IDs)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
--Not Null pour eviter les erreur de champs vides dans l'affichage des données dashboard
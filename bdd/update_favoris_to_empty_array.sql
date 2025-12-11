-- Migration: mettre à jour les valeurs NULL de la colonne `favoris`
-- Remplace les valeurs NULL par un tableau JSON vide '[]' pour éviter les vérifications côté client
-- Exécuter depuis le répertoire de la base ou via votre outil SQL (phpMyAdmin / mysql cli)

UPDATE utilisateurs
SET favoris = '[]'
WHERE favoris IS NULL;

-- NOTE:
-- Certains serveurs MySQL/MariaDB n'autorisent pas de "DEFAULT" sur les colonnes TEXT/BLOB.
-- Ce script n'altère pas le schéma pour rester compatible. Si vous souhaitez forcer
-- un comportement "NOT NULL"/valeur par défaut côté serveur, adaptez selon votre version
-- de MySQL (ex: convertir en JSON ou VARCHAR ou définir une valeur par défaut si supportée).

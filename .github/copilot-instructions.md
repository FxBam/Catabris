# Instructions pour les agents AI dans le projet Catabris

## Vue d'ensemble
Catabris est une application web utilisant PHP et MySQL, orchestrée avec Docker Compose. Elle inclut les services suivants :
- **web** : Serveur PHP avec Apache.
- **db** : Base de données MySQL.
- **phpmyadmin** : Interface de gestion MySQL.

## Points clés de l'architecture
- **Connexion à la base de données** :
  - Fichier : `connexion.php`
  - Utilise PDO pour se connecter à MySQL avec les paramètres définis dans `docker-compose.yml`.
  - Exemple de configuration :
    ```php
    $host = "db";
    $dbname = "catabris";
    $user = "user";
    $pass = "userpass";
    ```

- **Docker Compose** :
  - Définit les services et leurs dépendances.
  - Ports exposés :
    - Application web : [http://localhost:8080](http://localhost:8080)
    - phpMyAdmin : [http://localhost:8081](http://localhost:8081)
  - Volume persistant pour MySQL : `db_data`.

## Workflows de développement
### Lancer l'application
1. Assurez-vous que Docker est installé et en cours d'exécution.
2. Démarrez les services avec :
   ```powershell
   docker-compose up -d
   ```
3. Accédez à l'application sur [http://localhost:8080](http://localhost:8080).
4. phpMyAdmin est disponible sur [http://localhost:8081](http://localhost:8081).

### Arrêter les services
```powershell
docker-compose down
```

### Déboguer la base de données
- Utilisez phpMyAdmin pour inspecter les données ou exécuter des requêtes SQL.
- Identifiants par défaut :
  - Utilisateur : `root`
  - Mot de passe : `root`

## Conventions spécifiques
- **Gestion des erreurs** :
  - Les connexions à la base de données doivent gérer les exceptions avec `PDOException`.
- **Volumes** :
  - Le code source est monté dans le conteneur pour permettre un développement en direct.

## Fichiers importants
- `connexion.php` : Configuration de la base de données.
- `docker-compose.yml` : Définition des services Docker.
- `README.md` : Documentation générale du projet.

## Notes pour les agents AI
- Respectez les conventions de nommage et les configurations définies dans `docker-compose.yml`.
- Vérifiez les dépendances entre services avant de modifier les fichiers.
- Utilisez les ports et identifiants définis pour les tests et le développement.
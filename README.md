# 🐱 Catabris

**Version PHP / MySQL**  

Trouvez et explorez tous les équipements sportifs en France : terrains, gymnases, stades, salles spécialisées, infrastructures couvertes…  
Accédez à leurs caractéristiques complètes (accessibilité PMR, dimensions, type, sol, adresse, GPS…) et identifiez rapidement les lieux utilisables en cas de catastrophe naturelle.

---

## 🧭 Usage
Vous pourrez :  
- Visualiser les équipements sportifs sur une carte  
- Filtrer par type (basket, foot, gymnase, piscine…)  
- Afficher l’accessibilité (PMR, accès libre, réservation…)  
- Voir toutes les informations techniques  
- Analyser les installations appropriées pour des situations d’urgence (gymnases couverts, grandes surfaces, proximité, accessibilité…)

---

## 📥 Installation
- git clone https://github.com/YOUR-USERNAME/Catabris.git
- cd Catabris

---

## 📦 Structure du projet

### Services
- `web` → Serveur Apache + PHP  
- `db` → MySQL  
- `phpmyadmin` → Interface de gestion SQL  

### Fichiers importants
- `connexion.php` — connexion PDO à la base  
- `/src` — pages PHP et logique du site  
- `/assets` — CSS / JS / images  

---

## 🔍 Fonctionnalités principales - Recherche d’équipements sportifs
- Par commune / département / GPS  
- Par type d’infrastructure  
- Par critères (dimensions, surface, sol…)  

### Fiche détaillée
Chaque équipement expose :  
- Accessibilité PMR  
- Adresse complète  
- Coordonnées GPS  
- Type d’infrastructure  
- Dimensions & surface  
- Nature du sol  
- Propriétaire / ERP  
- Observations  
- Lien web  
- Disponibilité / règles d’accès  

### Mode “Situation d’urgence”
Pour les collectivités & services de secours :  
- Trouver les gymnases couverts disponibles  
- Filtrer par capacité / surface minimale  
- Prioriser les installations accessibles PMR  

**Utilisation possible comme :**  
- Centre d’accueil  
- Point de secours  
- Stockage d’urgence  

---

## 🛠️ Stack technique
- PHP 8  
- MySQL 8  
- PDO (connexion & sécurisation SQL)  
- Leaflet / OpenStreetMap (cartographie)  
- phpMyAdmin  

---

## 📖 Documentation officielle
Certaines données peuvent être enrichies via :  
👉 [API Data ES - Gouvernement](https://api.gouv.fr/documentation/api-data-es)

---

## ⭐ N’hésitez pas à mettre une étoile au dépôt si Catabris vous est utile !
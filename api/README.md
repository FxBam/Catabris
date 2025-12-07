# API Catabris - Documentation

## Endpoints disponibles

### 1. Liste des équipements avec pagination
**URL**: `/api/equipements.php`  
**Méthode**: GET

#### Paramètres
- `page` (optionnel) : Numéro de page (défaut: 1)
- `limit` (optionnel) : Nombre d'équipements par page (défaut: 20, max: 100)
- `q` (optionnel) : Recherche par nom ou commune
- `id` (optionnel) : Récupérer un équipement spécifique
- `minLat`, `maxLat`, `minLon`, `maxLon` (optionnel) : Filtrage géographique

#### Exemples d'utilisation

```bash
# Page 1 avec 20 équipements
http://localhost/Catabris/api/equipements.php?page=1&limit=20

# Recherche avec pagination
http://localhost/Catabris/api/equipements.php?page=1&limit=10&q=Paris

# Filtrage géographique
http://localhost/Catabris/api/equipements.php?minLat=48.8&maxLat=48.9&minLon=2.3&maxLon=2.4

# Récupérer un équipement spécifique
http://localhost/Catabris/api/equipements.php?id=123
```

#### Réponse JSON
```json
{
    "success": true,
    "page": 1,
    "limit": 20,
    "total_count": 150,
    "total_pages": 8,
    "count": 20,
    "data": [
        {
            "id": "1",
            "nom": "Stade Municipal",
            "type_equipement": "Stade",
            "commune": "Paris",
            "code_postal": "75001",
            "adresse": "1 rue du Sport",
            "latitude": 48.8566,
            "longitude": 2.3522,
            "proprietaire_principal_type": "Commune",
            "sanitaires": true,
            "acces_handi_mobilite": true,
            "creation_dt": "2024-01-01 10:00:00",
            "maj_date": "2024-12-01 15:30:00"
        }
    ]
}
```

---

### 2. Suggestions (Autocomplete)
**URL**: `/api/suggestions.php`  
**Méthode**: GET

#### Paramètres
- `q` (requis) : Terme de recherche
- `limit` (optionnel) : Nombre de suggestions (défaut: 10, max: 50)
- `minLat`, `maxLat`, `minLon`, `maxLon` (optionnel) : Prioriser par position

#### Exemples d'utilisation

```bash
# Recherche simple
http://localhost/Catabris/api/suggestions.php?q=stade

# Avec limite personnalisée
http://localhost/Catabris/api/suggestions.php?q=piscine&limit=5

# Prioriser par position géographique
http://localhost/Catabris/api/suggestions.php?q=terrain&minLat=48.8&maxLat=48.9&minLon=2.3&maxLon=2.4
```

#### Réponse JSON
```json
{
    "success": true,
    "count": 5,
    "suggestions": [
        {
            "id": "1",
            "label": "Stade Municipal - Paris",
            "nom": "Stade Municipal",
            "commune": "Paris",
            "type": "Stade",
            "lat": 48.8566,
            "lon": 2.3522
        }
    ]
}
```

---

### 3. Documentation de l'API
**URL**: `/api/index.php`  
**Méthode**: GET

Retourne la documentation complète de l'API en JSON.

---

## Utilisation avec JavaScript

### Exemple avec fetch (pagination)
```javascript
async function getEquipements(page = 1, limit = 20, query = '') {
    const params = new URLSearchParams({
        page: page,
        limit: limit
    });
    
    if (query) {
        params.append('q', query);
    }
    
    const response = await fetch(`/api/equipements.php?${params}`);
    const data = await response.json();
    
    console.log(`Page ${data.page} sur ${data.total_pages}`);
    console.log(`${data.count} équipements sur ${data.total_count} total`);
    
    return data;
}

// Utilisation
getEquipements(1, 20, 'Paris').then(data => {
    data.data.forEach(equip => {
        console.log(equip.nom, equip.commune);
    });
});
```

### Exemple avec fetch (suggestions)
```javascript
async function getSuggestions(query) {
    const response = await fetch(`/api/suggestions.php?q=${encodeURIComponent(query)}&limit=10`);
    const data = await response.json();
    
    return data.suggestions;
}

// Utilisation dans un input
document.querySelector('#search').addEventListener('input', async (e) => {
    const suggestions = await getSuggestions(e.target.value);
    // Afficher les suggestions
});
```

---

## Gestion des erreurs

En cas d'erreur, l'API retourne un code HTTP approprié et un objet JSON :

```json
{
    "success": false,
    "error": "Erreur serveur",
    "message": "Description détaillée de l'erreur"
}
```

---

## Notes importantes

1. **Performance** : La pagination limite le nombre d'équipements retournés, réduisant la charge serveur
2. **Cache** : Envisagez d'ajouter un système de cache pour les requêtes fréquentes
3. **Sécurité** : Tous les paramètres sont validés et échappés pour éviter les injections SQL
4. **CORS** : L'API accepte les requêtes cross-origin (`Access-Control-Allow-Origin: *`)

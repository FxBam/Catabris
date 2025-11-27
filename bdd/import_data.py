import os
import json
import mysql.connector
import sys
import re

# --- CONFIGURATION ---
DB_CONFIG = {
    "host": "localhost",
    "port": 3306,
    "user": "root",
    "password": "",
    "database": "catabris"
}
TABLE_NAME = "equipements_sportifs"
SOURCE_FILE = "data-es-equipement.json" # necessaire d'avoir ce fichier dans le meme dossier que ce script
ERR_LOG_FILE = "insert_equipement_err" # fichier contenant la liste qui a fait planté le script
REQUEST_BUFFER = 1000 # nb qu'on stocke avant d'envoyer à la db
auto = True # mode de sortie des requetes, true = directement dans la db et false = dans un fichier txt

# --- CONNEXION À LA BASE ---
print("Connexion à la base de données en cours...")
try:
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()
except mysql.connector.Error as err:
    print(f"Erreur: impossible de se connecter à la base de données MySQL, {DB_CONFIG['database']}. \nDétails: {err}")
    sys.exit(1)
except TypeError as err:
    print("Impossible de trouver le fichier d'environnement.")
    sys.exit(1)
print("Connexion à la base de données établie.")


# --- LECTURE DU FICHIER JSON ---
print("Chargement du fichier JSON...")

try:
    with open(SOURCE_FILE, "r", encoding="utf-8") as file:
        equipements = json.load(file)
    print(f"{len(equipements)} équipements chargés.")

except Exception as e:
    print(f"Erreur de lecture du fichier JSON: {e}")
    cursor.close()
    conn.close()
    sys.exit(1)


"""Convertit les valeurs JSON en chaînes SQL compatibles"""
def normalize(key, value):
    if value is None:
        return "NULL"
    
    invalid_values = ["is_date_homologation_known", "is_date_mise_en_service_known", "is_date_derniers_travaux_known"]
    if (key in invalid_values):
        if (str(value) == "Oui"): 
            return '1'
        if (str(value) == "Non"):
            return '0'
    
    if key == "activites_code":
        return f"'{str(value).replace(';', ',')}'"

    if key == "activites_json":
        if isinstance(value, str):
            # Si la valeur est une chaine, on essaye de corriger tout les defauts du fichier json
            try:
                n_value = str(value).replace(';', '","')
                n_value = str(n_value).replace(': }', ': "NULL"}')
                n_value = str(n_value).replace(', "', '"], "')
                n_value = str(n_value).replace('": "], "', '": "NULL", "')
                n_value = re.sub(r': ([A-Z])', r': ["\1', n_value)
                n_value = str(n_value).replace(' / ', ', ')
                n_value = str(n_value).replace('\n', ' ')
                n_value = n_value.replace('\'', '\\\'')

                return f"'{n_value}'"

            except json.JSONDecodeError:
                # Si le JSON est mal formé, on renvoie en string
                return f"'{value}'"
        else:
            return f"'{json.dumps(value)}'"

    # Convertir les listes en chaîne simple
    if isinstance(value, list):
        txt = ", ".join(map(str, value))
        txt = txt.replace('\'', '\\\'')
        return f"'{txt}'"

    # Convertir les dictionnaires (ex: coordonnees)
    if isinstance(value, dict):
        lat = value.get("lat")
        lon = value.get("lon")
        return f"'{lat}, {lon}'" if lat and lon else "NULL"

    # Valeurs numériques → pas de quotes
    if isinstance(value, (int, float)):
        return str(value)

    # Chaînes normales
    n_value = str(value).replace('\n', ' ')
    n_value = str(n_value).replace('\'', '\\\'')
    return f"'{n_value}'"


def build_insert_request(data, table_name="equipements_sportifs"):
    """Construit une requête SQL INSERT à partir du JSON."""
    mapping = {
        "famille": "type_famille",
        "code": "type_code"
    }

    for old, new in mapping.items():
        if old in data:
            v = data.pop(old)
            data[new] = v

    # on enleve et remet ces valeurs pour quelles retrouvent leur place d'origine dans la liste
    data['rnb_id'] = data.pop('rnb_id')
    data['commune'] = data.pop('commune')

    mapping_colonnes = {
    "installation_numero": "id",
    "installation_id": "installation_id",
    "creation_dt": "creation_dt",
    "maj_date": "maj_date",
    "maj_lien": "maj_lien",
    "numero": "numero",
    "nom": "nom",
    "type": "type_equipement",
    "coordonnees": "coordonnees",
    "proprietaire_principal_nom": "proprietaire_principal_nom",
    "proprietaire_principal_type": "proprietaire_principal_type",
    "autres_proprietaires": "autres_proprietaires",
    "proprietaire_secondaire_type": "proprietaire_secondaire_type",
    "gestionnaire_type": "gestionnaire_type",
    "co_gestionnaire_type": "co_gestionnaire_type",
    "gestion_dsp": "gestion_dsp",
    "arrete_ouverture": "arrete_ouverture",
    "erp_type": "erp_type",
    "erp_cat": "erp_cat",
    "is_date_homologation_known": "is_date_homologation_known",
    "homologation_date": "homologation_date",
    "homologation_periode": "homologation_periode",
    "is_date_mise_en_service_known": "is_date_mise_en_service_known",
    "mise_en_service_date": "mise_en_service_date",
    "mise_en_service_periode": "mise_en_service_periode",
    "is_date_derniers_travaux_known": "is_date_derniers_travaux_known",
    "derniers_travaux_date": "derniers_travaux_date",
    "derniers_travaux_periode": "derniers_travaux_periode",
    "derniers_travaux_type": "derniers_travaux_type",
    "chauffage_energie": "chauffage_energie",
    "nature": "nature_equipement",
    "aire_nature_sol": "aire_nature_sol",
    "aire_longueur": "aire_longueur",
    "aire_largeur": "aire_largeur",
    "aire_hauteur": "aire_hauteur",
    "aire_surface": "aire_surface",
    "aire_eclairage": "aire_eclairage",
    "aire_couloirs_nb": "aire_couloirs_nb",
    "places_tibune_nb": "places_tibune_nb",
    "vestiaires_sportifs_nb": "vestiaires_sportifs_nb",
    "vestiaires_arbitres_nb": "vestiaires_arbitres_nb",
    "douches": "douches",
    "sanitaires": "sanitaires",
    "autres_locaux": "autres_locaux",
    "amenagements_confort": "amenagements_confort",
    "acces_handi_mobilite": "acces_handi_mobilite",
    "acces_handi_sensoriel": "acces_handi_sensoriel",
    "is_pdesi_pdipr": "is_pdesi_pdipr",
    "bassin_longueur": "bassin_longueur",
    "bassin_largeur": "bassin_largeur",
    "bassin_surface": "bassin_surface",
    "bassin_profondeur_min": "bassin_profondeur_min",
    "bassin_profondeur_max": "bassin_profondeur_max",
    "piste_longueur": "piste_longueur",
    "sae_hauteur": "sae_hauteur",
    "sae_surface": "sae_surface",
    "sae_couloirs_nb": "sae_couloirs_nb",
    "pas_de_tir_type": "pas_de_tir_type",
    "website": "website",
    "utilisateurs": "utilisateurs",
    "acces_libre": "acces_libre",
    "ouverture_saisonniere": "ouverture_saisonniere",
    "activites": "activites",
    "observations": "observations",
    "coordonnees_y": "coordonnees_y",
    "coordonnees_x": "coordonnees_x",
    "activites_code": "activites_code",
    "activites_json": "activites_json",
    "completion_taux": "completion_taux",
    "equip_nb": "equip_nb",
    "equipement_id": "equipement_id",
    "etat": "etat",
    "type_famille": "type_famille",
    "type_code": "type_code",
    "rnb_id": "rnb_id",
    "commune": "commune"
    }

    # Filtrer et renommer directement ici
    columns = ", ".join(mapping_colonnes[json_key] for json_key in data if json_key in mapping_colonnes)
    values = ", ".join(normalize(k, v) for k, v in ((mapping_colonnes[json_key], value) for json_key, value in data.items() if json_key in mapping_colonnes))

    return f"INSERT INTO {table_name} ({columns}) VALUES ({values});"


print("Insertion en cours..")
requests = []
count = 1
duplicate_equipements_count = 0

if (not auto): 
    with open("test.txt", "w", encoding="utf-8") as out:
        for e in equipements[0:2500]: # modifier l'intervalle en fonction de vos besoins
            sql = build_insert_request(e)
            out.write("/*NB: " + str(count) + ",*/ " + sql + "\n")
            count += 1


if (auto):
    # --- TRUNCATE de la table pour effacer les ancinnes données---
    print(f"Suppression de contenu de la table {TABLE_NAME}")
    cursor.execute("SET FOREIGN_KEY_CHECKS = 0;")
    cursor.execute(f"TRUNCATE TABLE {TABLE_NAME};")
    cursor.execute("SET FOREIGN_KEY_CHECKS = 1;")
    conn.commit()
    print("Suppresion terminée.")
    
    for e in equipements:
        try:
            requests.append(build_insert_request(e))
            if (len(requests) == REQUEST_BUFFER):
                for req in requests:
                    try:
                        cursor.execute(req)
                    except mysql.connector.Error as err:
                        if ("1062 (23000): Duplicate entry" in str(err)):
                            duplicate_equipements_count += 1
                            count += 1
                            continue
                        else:
                            print(f"Erreur MySQL sur la requête n°{count} : {err}")
                    count += 1
                conn.commit()
                print(f"{count} lignes insérées...")
                requests = []
        except mysql.connector.Error as err:
            print(f"Erreur en lien avec mysql.connector: {err}")
            with open(ERR_LOG_FILE, "w", encoding="utf-8") as out:
                out.write("Erreur: " + str(err) + "\n\n\n" + "Requete numéro : " + str(count) + "\n\n\n" + str(req))
        except Exception as ex:
            print(f"Erreur inatendue : {ex}")

print("\n\n\n=====RECAP=====")
print(f"{count} équipements traités.")
print(f"Il y avait {duplicate_equipements_count} élements doublons dans le fichier {SOURCE_FILE}")
print("===============")
cursor.close()
conn.close()
print(f"Connexion MySQL fermée.")
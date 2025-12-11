import mysql.connector

# --- CONFIGURATION ---
DB_CONFIG = {
    "host": "mysql.infuseting.fr",
    "port": 3306,
    "user": "catabris",
    "password": "yZdXwjMC$fg5x^P5!8",
    "database": "catabris"
}

SQL_FILE = "equipements_sportifs.sql"  # ton fichier SQL (renomme-le)

print("Connexion à la base MySQL...")

try:
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()
    print("Connexion réussie !")
except mysql.connector.Error as err:
    print("Erreur de connexion :", err)
    exit(1)

print("Lecture du fichier SQL...")

try:
    with open(SQL_FILE, "r", encoding="utf-8") as f:
        sql_content = f.read()
except Exception as e:
    print("Erreur lecture fichier :", e)
    cursor.close()
    conn.close()
    exit(1)

print("Découpage des requêtes...")

queries = []
buffer = ""

for line in sql_content.splitlines():
    # ignorer commentaires
    stripped = line.strip()
    if stripped.startswith("--") or stripped.startswith("/*") or stripped.startswith("#"):
        continue

    buffer += line + "\n"
    # fin de requête détectée
    if stripped.endswith(";"):
        queries.append(buffer.strip())
        buffer = ""

# S'il reste quelque chose
if buffer.strip():
    queries.append(buffer.strip())

print(f"{len(queries)} requêtes détectées.\n")

success = 0
fail = 0

for i, q in enumerate(queries, start=1):
    print(f"Exécution requête {i}/{len(queries)}...")
    try:
        cursor.execute(q)
        conn.commit()
        success += 1
    except mysql.connector.Error as err:
        fail += 1
        print("\n❌ ERREUR SQL")
        print("Requête :")
        print(q)
        print("Erreur :")
        print(err)
        print("------\n")

print("\n===== RÉSUMÉ =====")
print(f"Requêtes exécutées avec succès : {success}")
print(f"Requêtes en erreur            : {fail}")

cursor.close()
conn.close()
print("Connexion fermée.")
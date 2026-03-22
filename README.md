# ⚡ SenWatt — Gestion Intelligente de la Consommation Électrique

Projet IoT — Module Introduction à l'IoT | Pr Moussa DIALLO | UCAD 2025

---

## 👥 Membres du groupe

| Membre | Rôle | Branche GitHub |
|---|---|---|
| M1 — Mouhamadou Rassoul NAME | Chef de projet & Base de données | `feature/database` |
| M2 — Salimata Sène DIOP | Node-RED Flow données & alertes | `feature/flow-mqtt-data` |
| M3 — Khadidiatiou NIAKH | Node-RED API REST & pilotage | `feature/flow-api-pilotage` |
| M4 — Papa Ousmane MANÉ | Dashboard Web PHP | `feature/dashboard-php` |
| M5 — Baye Amadou THIAM | Simulation MQTT & Documentation | `feature/simulation-mqtt` |

---

## 🏗️ Architecture du projet
```
senwatt/
├── database/
│   ├── senwatt.sql        # Script SQL complet (tables + données de test)
│   └── config.php         # Connexion MySQL — NE PAS PUSHER sur GitHub
├── node-red/
│   ├── flow_mqtt_data.json     # Flow Node-RED : MQTT → calculs → MySQL
│   └── flow_api_pilotage.json  # Flow Node-RED : API REST + commandes ON/OFF
├── dashboard/
│   ├── index.php          # Page de connexion
│   ├── dashboard.php      # Dashboard temps réel
│   └── historique.php     # Historique et graphes
├── simulation/
│   └── simulate_mqtt.py   # Script Python simulant les prises intelligentes
├── docs/
│   └── fiche_technique.md # Documentation théorique des capteurs
├── .gitignore
└── README.md
```

---

## 🔧 Installation & Configuration

### Prérequis
- Node-RED installé localement
- Python 3.x installé
- Accès à phpMyAdmin (Alwaysdata)

### Étape 1 — Cloner le projet
```bash
git clone https://github.com/rassoul7/SenWatt.git
cd senwatt
```

### Étape 2 — Créer le fichier config.php
Crée le fichier `database/config.php` avec les identifiants partagés par M1 :
```php
<?php
$db_host = 'mysql-senwatt.alwaysdata.net';
$db_name = 'senwatt_senwatt_db';
$db_user = 'senwatt_senwatt_user';
$db_pass = 'Mouss@Diallo7';

$pdo = new PDO(
  "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
  $db_user,
  $db_pass,
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
?>
```

### Étape 3 — Initialiser la base de données
1. Connecte-toi à phpMyAdmin via le panel Alwaysdata
2. Sélectionne la base `senwatt_db`
3. Onglet **SQL** → colle le contenu de `database/senwatt.sql` → **Exécuter**

### Étape 4 — Importer les flows Node-RED
1. Ouvre Node-RED dans ton navigateur
2. Menu hamburger → **Import**
3. Importe `node-red/flow_mqtt_data.json`
4. Importe `node-red/flow_api_pilotage.json`
5. Clique **Deploy**

### Étape 5 — Lancer la simulation MQTT
```bash
cd simulation
pip install paho-mqtt
python simulate_mqtt.py
```

### Étape 6 — Accéder au dashboard
```
https://senwatt.alwaysdata.net
```
- Email : `admin@senwatt.com`
- Mot de passe : `senwatt2025`

---

## 🌐 Identifiants MQTT (EMQX Cloud)

| Paramètre | Valeur |
|---|---|
| Host | `ub0610cc.ala.eu-central-1.emqxsl.com` |
| Port | `1883` |
| Username | `senwatt` |
| Password | `Mouss@Diallo7` |

### Topics utilisés

| Topic | Description |
|---|---|
| `senwatt/user01/clim_salon/data` | Données climatiseur |
| `senwatt/user01/frigo/data` | Données réfrigérateur |
| `senwatt/user01/tv_salon/data` | Données télévision |
| `senwatt/user01/fer_repasser/data` | Données fer à repasser |
| `senwatt/user01/{prise_id}/cmd` | Commandes ON/OFF |

---

## 📊 Format des messages MQTT

### Publish (prise → broker)
```json
{
  "user_id": 1,
  "prise_id": "clim_salon",
  "nom_appareil": "Climatiseur",
  "tension": 230,
  "courant": 7.83,
  "puissance": 1800,
  "timestamp": "2025-03-22 14:30:00"
}
```

### Subscribe (broker → prise) — commande ON/OFF
```json
{
  "prise_id": "clim_salon",
  "etat": "OFF"
}
```

---

## 🔌 API REST — Endpoints disponibles

| Méthode | URL | Description |
|---|---|---|
| GET | `/api/mesures?prise_id=X` | Dernières mesures d'une prise |
| GET | `/api/historique?prise_id=X&date=Y` | Historique par prise et date |
| POST | `/api/commande` | Envoyer une commande ON/OFF |

---

## 🚨 Scénario de démo

1. **M5** lance `simulate_mqtt.py` — les prises publient leurs données
2. **M4** ouvre le dashboard — les données s'affichent en temps réel
3. **M4** clique **Éteindre** sur le climatiseur — Node-RED publie la commande
4. **M5** montre la réception de la commande dans le terminal
5. **M5** déclenche la simulation de délestage SENELEC
6. **M4** montre l'alerte **Délestage détecté** sur le dashboard
7. **M2/M3** montrent les flows Node-RED ouverts
8. **M1** montre la base de données avec les mesures insérées

---

## ⚠️ Règles GitHub

- Ne **jamais** pusher `database/config.php` (contient les mots de passe)
- Chaque membre travaille **uniquement sur sa branche**
- Messages de commit clairs : `"Ajout noeud function calcul kWh"`
- Prévenir M1 quand ta branche est prête pour la fusion

---

## 📅 Planning

| Jour | Tâche | Responsable |
|---|---|---|
| J1 | Alwaysdata + EMQX + GitHub + SQL | M1 |
| J2 | Topics MQTT définis | M2 |
| J3 | Endpoints API définis | M3 |
| J4 | Flows Node-RED testés | M2 + M3 |
| J5-6 | Tests end-to-end + corrections | Tous |
| J7-14 | Rapport + slides + démo | Tous |
-- =============================================
-- SenWatt — Script SQL complet
-- UCAD IoT 2025
-- =============================================

-- Table utilisateurs
CREATE TABLE IF NOT EXISTS utilisateurs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table consommation
CREATE TABLE IF NOT EXISTS consommation (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  prise_id VARCHAR(50) NOT NULL,
  nom_appareil VARCHAR(100),
  tension FLOAT,
  courant FLOAT,
  puissance FLOAT,
  energie_kwh FLOAT,
  cout_fcfa FLOAT,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES utilisateurs(id)
);

-- Table alertes
CREATE TABLE IF NOT EXISTS alertes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  prise_id VARCHAR(50),
  type_alerte VARCHAR(100),
  message TEXT,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES utilisateurs(id)
);

-- =============================================
-- Données de test
-- =============================================

-- Un utilisateur de test
INSERT INTO utilisateurs (nom, email, password) VALUES 
('Admin SenWatt', 'admin@senwatt.com', MD5('senwatt2025'));

-- Mesures simulées
INSERT INTO consommation (user_id, prise_id, nom_appareil, tension, courant, puissance, energie_kwh, cout_fcfa) VALUES
(1, 'clim_salon', 'Climatiseur', 230, 7.83, 1800, 0.90, 135),
(1, 'frigo', 'Réfrigérateur', 230, 0.65, 150, 0.075, 11),
(1, 'tv_salon', 'Télévision', 230, 0.52, 120, 0.06, 9),
(1, 'fer_repasser', 'Fer à repasser', 230, 8.70, 2000, 1.0, 150);

-- Alertes simulées
INSERT INTO alertes (user_id, prise_id, type_alerte, message) VALUES
(1, 'clim_salon', 'Seuil dépassé', 'Puissance > 3500W détectée sur clim_salon'),
(1, NULL, 'Délestage', 'Aucune donnée reçue depuis 3 minutes — délestage SENELEC probable');
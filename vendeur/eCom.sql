-- Table des utilisateurs (clients et vendeurs)
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_utilisateur VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe_hash VARCHAR(255) NOT NULL,
    nom_complet VARCHAR(100),
    telephone VARCHAR(20),
    adresse TEXT,
    ville VARCHAR(50),
    code_postal VARCHAR(20),
    pays VARCHAR(50),
    role_utilisateur ENUM('client', 'vendeur', 'admin') NOT NULL DEFAULT 'client',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des catégories de produits
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT
);

-- Table des produits
CREATE TABLE produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendeur_id INT,
    categorie_id INT,
    nom VARCHAR(150) NOT NULL,
    description TEXT,
    prix DECIMAL(10, 2) NOT NULL,
    quantite_stock INT DEFAULT 0,
    url_image VARCHAR(255),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vendeur_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (categorie_id) REFERENCES categories(id)
);

-- Table des commandes
CREATE TABLE commandes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT,
    statut_commande ENUM('en_attente', 'payée', 'expédiée', 'livrée', 'annulée') DEFAULT 'en_attente',
    montant_total DECIMAL(10, 2),
    adresse_livraison TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);

-- Détails des produits dans une commande
CREATE TABLE articles_commande (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT,
    produit_id INT,
    quantite INT NOT NULL,
    prix DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (commande_id) REFERENCES commandes(id),
    FOREIGN KEY (produit_id) REFERENCES produits(id)
);

-- Table des livraisons
CREATE TABLE livraisons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT,
    statut_livraison ENUM('en_attente', 'en_transit', 'livrée') DEFAULT 'en_attente',
    numero_suivi VARCHAR(100),
    date_expedition DATETIME,
    date_livraison DATETIME,
    FOREIGN KEY (commande_id) REFERENCES commandes(id)
);

-- Table des achats internes (ravitaillement)
CREATE TABLE achats_internes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produit_id INT,
    quantite INT,
    date_achat DATE,
    statut ENUM('en_attente', 'reçu') DEFAULT 'en_attente',
    notes TEXT,
    FOREIGN KEY (produit_id) REFERENCES produits(id)
);
-- CATEGORIE
CREATE TABLE categorie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL
);

-- PRODOTTI (menu)
CREATE TABLE prodotti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descrizione TEXT,
    prezzo DECIMAL(6,2) NOT NULL,
    categoria_id INT,
    disponibile BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (categoria_id) REFERENCES categorie(id) ON DELETE SET NULL

);

-- ORDINI
CREATE TABLE ordini (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_ordine TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    stato ENUM('in preparazione', 'pronto', 'completato') DEFAULT 'in preparazione',
    totale DECIMAL(7,2)
);

-- DETTAGLI ORDINE
CREATE TABLE dettagli_ordine (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ordine_id INT,
    prodotto_id INT,
    quantita INT NOT NULL,
    prezzo_unitario DECIMAL(6,2),
    FOREIGN KEY (ordine_id) REFERENCES ordini(id) ON DELETE CASCADE,
    FOREIGN KEY (prodotto_id) REFERENCES prodotti(id) ON DELETE CASCADE
);

--CREAZIONE DEGLI UTENTI DEL DATABASE--
CREATE TABLE utenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    ruolo_id INT DEFAULT 1
);

--Sql Street Food--

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
    FOREIGN KEY (categoria_id) REFERENCES categorie(id)
);

-- ORDINI
CREATE TABLE ordini (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_ordine TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    stato ENUM('in preparazione', 'pronto', 'completato') DEFAULT 'in preparazione',
    totale DECIMAL(7,2)
);

-- DETTAGLI ORDINE (relazione N:N)
CREATE TABLE dettagli_ordine (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ordine_id INT,
    prodotto_id INT,
    quantita INT NOT NULL,
    prezzo_unitario DECIMAL(6,2),
    FOREIGN KEY (ordine_id) REFERENCES ordini(id),
    FOREIGN KEY (prodotto_id) REFERENCES prodotti(id)
);
-- =========================================================
-- DATABASE
-- =========================================================

CREATE DATABASE IF NOT EXISTS streetfood_db;
USE streetfood_db;

-- =========================================================
-- UTENTI
-- =========================================================

CREATE TABLE utenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    ruolo ENUM('root', 'cliente') NOT NULL DEFAULT 'cliente'
) ENGINE=InnoDB;

-- =========================================================
-- CATEGORIE
-- =========================================================

CREATE TABLE categorie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- =========================================================
-- PRODOTTI
-- =========================================================

CREATE TABLE prodotti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descrizione TEXT,
    prezzo DECIMAL(6,2) NOT NULL,
    categoria_id INT NULL,
    disponibile BOOLEAN DEFAULT TRUE,

    CONSTRAINT fk_prodotti_categoria
        FOREIGN KEY (categoria_id)
        REFERENCES categorie(id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================================
-- ORDINI
-- =========================================================

CREATE TABLE ordini (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_ordine TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    stato ENUM(
        'in preparazione',
        'pronto',
        'completato'
    ) DEFAULT 'in preparazione',

    totale DECIMAL(7,2) NOT NULL,

    utente_id INT NOT NULL,

    CONSTRAINT fk_ordini_utenti
        FOREIGN KEY (utente_id)
        REFERENCES utenti(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- DETTAGLI ORDINE
-- =========================================================

CREATE TABLE dettagli_ordine (
    id INT AUTO_INCREMENT PRIMARY KEY,

    ordine_id INT NOT NULL,
    prodotto_id INT NOT NULL,

    quantita INT NOT NULL,
    prezzo_unitario DECIMAL(6,2) NOT NULL,

    CONSTRAINT fk_dettagli_ordine
        FOREIGN KEY (ordine_id)
        REFERENCES ordini(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_dettagli_prodotto
        FOREIGN KEY (prodotto_id)
        REFERENCES prodotti(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

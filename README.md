# 🍔 Sql-Street-Food

> Web Application per la gestione di prodotti e ordini street food  
> Progetto sviluppato da Ardente & Piazzalunga

---

# 📌 Descrizione del progetto

Sql-Street-Food è una web application sviluppata in PHP e MySQL che permette la gestione di un sistema di ordinazioni per uno street food.

L’applicazione consente agli utenti autenticati di:
- visualizzare il menu dei prodotti
- consultare le categorie disponibili
- creare nuovi ordini
- visualizzare lo storico degli ordini effettuati

Gli amministratori (`root`) possono inoltre:
- eliminare ordini
- gestire il catalogo prodotti
- controllare l’intero sistema

Il progetto utilizza un database relazionale MySQL con relazioni tra utenti, prodotti, categorie e ordini.

---

# 👨‍💻 Autori
- Ardente
- Piazzalunga

---

# 🗄️ Struttura del database

Il database è composto dalle seguenti tabelle principali.

---

## 1. utenti
Contiene gli utenti registrati nel sistema.

### Campi
- `id` (PK)
- `username`
- `password`
- `ruolo`

### Note
Il campo `ruolo` distingue:
- utenti normali
- amministratori (`root`)

---

## 2. categorie
Contiene le categorie dei prodotti.

### Campi
- `id` (PK)
- `nome`

### Esempi
- Burger
- Bevande
- Dolci

---

## 3. prodotti
Contiene i prodotti presenti nel menu.

### Campi
- `id` (PK)
- `nome`
- `descrizione`
- `prezzo`
- `categoria_id` (FK)
- `disponibile`

### Note
Ogni prodotto appartiene a una categoria.

---

## 4. ordini
Contiene gli ordini effettuati dagli utenti.

### Campi
- `id` (PK)
- `utente_id` (FK)
- `data_ordine`
- `stato`
- `totale`

### Note
Ogni ordine è associato a un utente.

---

## 5. dettagli_ordine
Tabella di associazione tra ordini e prodotti.

### Campi
- `id` (PK)
- `ordine_id` (FK)
- `prodotto_id` (FK)
- `quantita`
- `prezzo_unitario`

### Note
Permette di salvare:
- i prodotti acquistati
- la quantità
- il prezzo al momento dell’ordine

---

# 🔗 Relazioni

- Una categoria può contenere più prodotti → `1 : N`
- Un utente può effettuare più ordini → `1 : N`
- Un ordine può contenere più prodotti → `N : N`
- La relazione tra ordini e prodotti è gestita tramite `dettagli_ordine`

---

# ⚙️ Funzionalità implementate

## ✅ Gestione utenti
- Login tramite sessione PHP
- Controllo permessi
- Gestione ruolo amministratore (`root`)

---

## ✅ Gestione prodotti
- Visualizzazione menu
- Inserimento prodotti
- Modifica prodotti
- Eliminazione prodotti
- Controllo disponibilità

---

## ✅ Gestione ordini
- Creazione nuovi ordini
- Visualizzazione ordini utente
- Visualizzazione globale ordini per amministratore
- Calcolo automatico totale ordine

---

## ✅ Gestione dettagli ordine
- Associazione prodotti ↔ ordini
- Quantità acquistata
- Prezzo unitario salvato nel database

---

# 🔒 Sicurezza e integrità

Il progetto utilizza:
- query preparate (`prepare`)
- protezione SQL Injection
- sessioni PHP
- gestione transazioni ACID (`BEGIN WORK`, `COMMIT`, `ROLLBACK`)

---

# 🛠️ Tecnologie utilizzate

- HTML5
- CSS3
- PHP
- MySQL
- PDO

---

# 🚀 Avvio del progetto

## 1. Installare un server locale
Installare uno dei seguenti software:
- XAMPP
- WAMP
- Laragon

---

## 2. Copiare il progetto
Inserire la cartella del progetto dentro:
```text
Esempio:
```text
C:/xampp/htdocs/Sql-Street-Food
```

---

## 3. Avviare Apache e MySQL
Dal pannello di controllo di XAMPP:
- avviare Apache
- avviare MySQL

---

## 4. Creare il database
Aprire:
```text
http://localhost/phpmyadmin
```

Creare un nuovo database e importare il file:
```text
database.sql
```

---

## 5. Configurare la connessione
Modificare il file:
```text
config.php
```

Inserendo:
```php
$pdo = new PDO(
    "mysql:host=localhost;dbname=streetfood",
    "root",
    ""
);
```

---

## 6. Avviare il progetto
Aprire il browser e andare su:
```text
http://localhost/Sql-Street-Food
```

---

# 📂 Contenuto della consegna

- File PHP del progetto
- Script SQL del database
- README.md
- File CSS
- Gestione sessioni e login

---

# ⚠️ Note

- Il database utilizza chiavi primarie e chiavi esterne
- Sono presenti vincoli relazionali
- Le transazioni garantiscono coerenza dei dati
- Alcune operazioni sono disponibili solo agli amministratori

---


Il progetto realizza una web application completa per la gestione di un sistema street food tramite database relazionale MySQL.

Sono state implementate:
- operazioni CRUD
- gestione ordini
- autenticazione utenti
- controllo permessi
- relazioni tra tabelle
- utilizzo di PHP con PDO
- gestione transazioni ACID

# 📝 Note aggiuntive
- Alcune parti del CSS e questo readme sono state realizzate con supporto di strumenti di intelligenza artificiale.

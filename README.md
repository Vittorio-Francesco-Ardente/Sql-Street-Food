# 🍔 Sql-Street-Food
> Web Application per la gestione di ordini street food  
> by Ardente & Piazzalunga

---

## 📌 Descrizione del progetto
SQl-Street-Food è una web application che consente di visualizzare un menu di prodotti street food e gestire gli ordini in modo semplice ed intuitivo.

L’applicazione permette di:
- consultare il menu diviso per categorie
- creare nuovi ordini
- visualizzare gli ordini effettuati
- gestire i prodotti (aggiunta, modifica, eliminazione)

---

## 👨‍💻 Autori
- Ardente  
- Piazzalunga  

---

## 🗄️ Struttura del database

Il database è composto da 4 tabelle principali:

### 1. categorie
Contiene le categorie dei prodotti (es. Burger, Bevande)
- id (PK)
- nome

### 2. prodotti
Contiene i prodotti del menu
- id (PK)
- nome
- descrizione
- prezzo
- categoria_id (FK)
- disponibile

### 3. ordini
Contiene gli ordini effettuati
- id (PK)
- data_ordine
- stato
- totale

### 4. dettagli_ordine
Associa prodotti agli ordini (relazione N:N)
- id (PK)
- ordine_id (FK)
- prodotto_id (FK)
- quantita
- prezzo_unitario

---

## 🔗 Relazioni
- Una categoria può contenere più prodotti (1:N)
- Un ordine può contenere più prodotti (1:N)
- I prodotti sono collegati agli ordini tramite la tabella dettagli_ordine (N:N)

---

## ⚙️ Funzionalità implementate

### ✅ CRUD completo
- Inserimento prodotti
- Visualizzazione menu
- Modifica prodotti
- Eliminazione prodotti

### 🛒 Gestione ordini
- Creazione ordine
- Aggiunta prodotti all’ordine
- Calcolo totale

### 🔍 Extra
- Filtro per categoria
- Stato ordine (in preparazione, pronto, completato)

---

## 🛠️ Tecnologie utilizzate
- HTML / CSS
- PHP
- MySQL

---

## 🚀 Istruzioni per l’avvio

1. Importare il file `.sql` nel database MySQL
2. Configurare la connessione al database nel file PHP
3. Avviare un server locale (XAMPP / WAMP)
4. Aprire il progetto nel browser

---

## 📂 Contenuto della consegna
- File sorgenti della web app
- Script SQL per il database
- README (questo file)

---

## ⚠️ Note
- I vincoli `ON DELETE CASCADE` permettono di mantenere la coerenza dei dati
- Il sistema è progettato per essere semplice ma funzionale

## ✅ Conclusione
Il progetto soddisfa tutti i requisiti richiesti, implementando una web application completa con database relazionale e operazioni CRUD.
``

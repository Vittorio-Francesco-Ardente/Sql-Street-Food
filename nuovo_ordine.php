<?php
require 'config.php';

session_start();

if (!isset($_SESSION['id']))
{
    die("Accesso negato");
}

$idUtente = $_SESSION['id'];

$sqlProdotti = "SELECT id, nome, prezzo, disponibile
                 FROM prodotti
                 WHERE disponibile = 1
                 ORDER BY nome";

$stmProdotti = $pdo->prepare($sqlProdotti);
$stmProdotti->execute();

$prodotti = $stmProdotti->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    try
    {
        $pdo->exec("SET SESSION innodb_lock_wait_timeout = 10");
        $pdo->exec("SET autocommit = 0");
        $pdo->exec("BEGIN WORK");

        $prodottiSelezionati = $_POST['prodotto'];
        $quantita = $_POST['quantita'];

        $totale = 0;

        foreach ($prodottiSelezionati as $index => $idProdotto)
        {
            $qta = $quantita[$index];

            $sqlPrezzo = "SELECT prezzo
                           FROM prodotti
                           WHERE id = ?";

            $stmPrezzo = $pdo->prepare($sqlPrezzo);
            $stmPrezzo->execute([$idProdotto]);

            $prezzo = $stmPrezzo->fetchColumn();

            $totale += ($prezzo * $qta);
        }

        $sqlOrdine = "INSERT INTO ordini (utente_id, data_ordine, stato, totale)
                       VALUES (?, NOW(), 'in elaborazione', ?)";

        $stmOrdine = $pdo->prepare($sqlOrdine);
        $stmOrdine->execute([$idUtente, $totale]);

        $idOrdine = $pdo->lastInsertId();

        $sqlDettaglio = "INSERT INTO dettagli_ordine (ordine_id, prodotto_id, quantita, prezzo_unitario)
                          VALUES (?, ?, ?, ?)";

        $stmDettaglio = $pdo->prepare($sqlDettaglio);

        foreach ($prodottiSelezionati as $index => $idProdotto)
        {
            $qta = $quantita[$index];

            $sqlPrezzo = "SELECT prezzo
                           FROM prodotti
                           WHERE id = ?";

            $stmPrezzo = $pdo->prepare($sqlPrezzo);
            $stmPrezzo->execute([$idProdotto]);
          
            $prezzo = $stmPrezzo->fetchColumn();

            $stmDettaglio->execute([
                $idOrdine,
                $idProdotto,
                $qta,
                $prezzo
            ]);
        }
        $pdo->exec("COMMIT");

        header("Location: home.php");
        exit();
    }
    catch (Exception $e)
    {
        $pdo->exec("ROLLBACK");

        die("Errore: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>NUOVO ORDINE</title>

<style>
body{
    font-family: Arial;
    margin: 30px;
    background-color: #f4f4f4;
}
.container{
    background-color: white;
    padding: 20px;
    border: 1px solid #ccc;
}
h1{
    color: #333;
}
select,
input{
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
}
button{
    padding: 10px 15px;
    border: none;
    background-color: #333;
    color: white;
    cursor: pointer;
}
button:hover{
    background-color: #555;
}
.riga{
    border-bottom: 1px solid #ccc;
    margin-bottom: 20px;
    padding-bottom: 20px;
}
</style>
</head>
<body>

<div class="container">
<h1>NUOVO ORDINE</h1>
<form method="POST">
    <?php for ($i = 0; $i < 3; $i++): ?>
        <div class="riga">
            <label>Prodotto</label>
            <select name="prodotto[]">
                <?php foreach ($prodotti as $p): ?>
                    <option value="<?= $p['id'] ?>">
                        <?= $p['nome'] ?> - <?= $p['prezzo'] ?> €
                    </option>
                <?php endforeach; ?>
            </select>
            <label>Quantità</label>
            <input type="number"
                name="quantita[]"
                min="1"
                value="1"
                required>
        </div>
  
    <?php endfor; ?>
    <button type="submit">CREA ORDINE</button>
</form>
  
<br>
<a href="home.php">
    <button>Torna alla home</button>
</a>
</div>
  
</body>
</html>

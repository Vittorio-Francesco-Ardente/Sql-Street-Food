<?php
session_start();
require 'config.php';
 
if (!isset($_SESSION['utente_id'])) {
    die("Accesso negato");
}
 
$idUtente = $_SESSION['utente_id'];
 
/* ---- PRODOTTI ---- */
$prodotti = $pdo->query("
    SELECT id, nome, prezzo
    FROM prodotti
    WHERE disponibile = 1
    ORDER BY nome
")->fetchAll(PDO::FETCH_ASSOC);
 
/* ---- CREA ORDINE ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    try {
 
        $pdo->beginTransaction();
 
        $idProdotti = $_POST['prodotto'];
        $quantita = $_POST['quantita'];
 
        $totale = 0;
 
        /* ---- calcolo totale ---- */
        foreach ($idProdotti as $i => $idProdotto) {
 
            $qta = (int)$quantita[$i];
 
            if ($qta < 1) $qta = 1;
 
            $stmt = $pdo->prepare("SELECT prezzo FROM prodotti WHERE id = ?");
            $stmt->execute([$idProdotto]);
            $prezzo = $stmt->fetchColumn();
 
            $totale += $prezzo * $qta;
        }
 
        /* ---- crea ordine ---- */
        $stmt = $pdo->prepare("
            INSERT INTO ordini (utente_id, data_ordine, stato, totale)
            VALUES (?, NOW(), 'in preparazione', ?)
        ");
 
        $stmt->execute([$idUtente, $totale]);
 
        $idOrdine = $pdo->lastInsertId();
 
        /* ---- dettagli ---- */
        $stmt = $pdo->prepare("
            INSERT INTO dettagli_ordine
            (ordine_id, prodotto_id, quantita, prezzo_unitario)
            VALUES (?, ?, ?, ?)
        ");
 
        foreach ($idProdotti as $i => $idProdotto) {
 
            $qta = (int)$quantita[$i];
 
            $stmtPrice = $pdo->prepare("SELECT prezzo FROM prodotti WHERE id = ?");
            $stmtPrice->execute([$idProdotto]);
            $prezzo = $stmtPrice->fetchColumn();
 
            $stmt->execute([
                $idOrdine,
                $idProdotto,
                $qta,
                $prezzo
            ]);
        }
 
        $pdo->commit();
 
        header("Location: home.php?success=1");
        exit;
 
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Errore: " . $e->getMessage());
    }
}
?>
 
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Nuovo Ordine</title>
 
<style>
body{font-family:Arial;margin:30px;background:#f4f4f4}
.box{background:white;padding:20px;max-width:600px}
.riga{border:1px solid #ddd;padding:10px;margin-bottom:10px}
select,input,button{width:100%;padding:8px;margin-top:5px}
button{background:#333;color:white;border:none;cursor:pointer}
.add{background:green;margin-bottom:10px}
</style>
 
<script>
function aggiungiRiga() {
 
    const container = document.getElementById("righe");
 
    const html = `
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
        <input type="number" name="quantita[]" value="1" min="1">
 
    </div>`;
 
    container.insertAdjacentHTML("beforeend", html);
}
</script>
 
</head>
<body>
 
<div class="box">
 
<h1>NUOVO ORDINE</h1>
 
<form method="POST">
 
    <div id="righe">
 
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
            <input type="number" name="quantita[]" value="1" min="1">
 
        </div>
 
    </div>
 
    <button type="button" class="add" onclick="aggiungiRiga()">a
        + AGGIUNGI PRODOTTO
    </button>
 
    <button type="submit">CREA ORDINE</button>
 
</form>
 
<br>
 
<a href="home.php">Torna alla home</a>
 
</div>
 
</body>
</html>

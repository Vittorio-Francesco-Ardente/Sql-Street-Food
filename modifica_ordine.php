<?php
require 'config.php';
session_start();

if (!isset($_SESSION['id'])) {
    die("Accesso negato");
}

$idUtente = $_SESSION['id'];
$idOrdine = $_GET['id'] ?? null;

if (!$idOrdine) {
    die("Ordine non valido");
}

$sqlProdotti = "SELECT id, nome, prezzo, disponibile FROM prodotti WHERE disponibile = 1";
$stm = $pdo->prepare($sqlProdotti);
$stm->execute();
$prodotti = $stm->fetchAll(PDO::FETCH_ASSOC);

$sqlOrdine = "SELECT * FROM ordini WHERE id = ? AND utente_id = ?";
$stmOrdine = $pdo->prepare($sqlOrdine);
$stmOrdine->execute([$idOrdine, $idUtente]);
$ordine = $stmOrdine->fetch(PDO::FETCH_ASSOC);

if (!$ordine) {
    die("Ordine non trovato");
}

$sqlDettagli = "SELECT * FROM dettagli_ordine WHERE ordine_id = ?";
$stmDettagli = $pdo->prepare($sqlDettagli);
$stmDettagli->execute([$idOrdine]);
$detagli = $stmDettagli->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $pdo->beginTransaction();

        $del = $pdo->prepare("DELETE FROM dettagli_ordine WHERE ordine_id = ?");
        $del->execute([$idOrdine]);

        $prodottiSelezionati = $_POST['prodotto'];
        $quantita = $_POST['quantita'];

        $totale = 0;

        $getPrezzo = $pdo->prepare("SELECT prezzo FROM prodotti WHERE id = ?");
        $insertDettaglio = $pdo->prepare(
            "INSERT INTO dettagli_ordine (ordine_id, prodotto_id, quantita, prezzo_unitario)
             VALUES (?, ?, ?, ?)"
        );

        foreach ($prodottiSelezionati as $i => $idProdotto) {

            $qta = (int)$quantita[$i];

            $getPrezzo->execute([$idProdotto]);
            $prezzo = $getPrezzo->fetchColumn();

            $totale += $prezzo * $qta;

            $insertDettaglio->execute([
                $idOrdine,
                $idProdotto,
                $qta,
                $prezzo
            ]);
        }

        $update = $pdo->prepare("UPDATE ordini SET totale = ?, stato = 'in elaborazione' WHERE id = ?");
        $update->execute([$totale, $idOrdine]);

        $pdo->commit();

        header("Location: home.php");
        exit();

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
<title>MODIFICA ORDINE</title>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

:root{
    --bg:#060816;
    --card:#0f172a;
    --border:rgba(255,255,255,0.08);
    --primary:#2563eb;
    --text:#f8fafc;
}

body{
    font-family:'Inter',sans-serif;
    margin:0;
    padding:40px;
    background:var(--bg);
    color:var(--text);
}

.container{
    max-width:900px;
    margin:auto;
}

h1{
    font-size:34px;
    margin-bottom:25px;
}

.box{
    background:var(--card);
    border:1px solid var(--border);
    padding:25px;
    border-radius:20px;
    margin-bottom:20px;
}

select,input{
    width:100%;
    padding:12px;
    margin-top:8px;
    margin-bottom:15px;
    border-radius:12px;
    border:1px solid var(--border);
    background:#0b1120;
    color:white;
}

button{
    padding:12px 18px;
    border:none;
    border-radius:12px;
    background:linear-gradient(135deg,#2563eb,#0ea5e9);
    color:white;
    font-weight:600;
    cursor:pointer;
    margin-right:10px;
}

.riga{
    border-bottom:1px solid var(--border);
    padding-bottom:15px;
    margin-bottom:15px;
}
</style>
</head>
<body>

<div class="container">

<h1>MODIFICA ORDINE #<?= $idOrdine ?></h1>

<form method="POST">

<?php for ($i = 0; $i < 3; $i++): ?>
    <div class="box riga">

        <label>Prodotto</label>
        <select name="prodotto[]">
            <?php foreach ($prodotti as $p): ?>
                <option value="<?= $p['id'] ?>">
                    <?= $p['nome'] ?> - <?= $p['prezzo'] ?> €
                </option>
            <?php endforeach; ?>
        </select>

        <label>Quantità</label>
        <input type="number" name="quantita[]" min="1" value="1">

    </div>
<?php endfor; ?>

<button type="submit">SALVA MODIFICHE</button>
<a href="home.php"><button type="button">ANNULLA</button></a>

</form>

</div>

</body>
</html>

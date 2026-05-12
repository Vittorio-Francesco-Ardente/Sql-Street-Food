<?php
session_start();
require 'config.php';

/* ---- CONTROLLO LOGIN ---- */
if (!isset($_SESSION['utente_id'], $_SESSION['utente_ruolo'])) {
    die("Accesso negato");
}

$idUtente = $_SESSION['utente_id'];
$ruolo = $_SESSION['utente_ruolo'];
$idOrdine = $_GET['id'] ?? null;

if (!$idOrdine) {
    die("Ordine non valido");
}

/* ---- PRODOTTI DISPONIBILI ---- */
$sqlProdotti = "
    SELECT id, nome, prezzo, disponibile 
    FROM prodotti 
    WHERE disponibile = 1
";

$stm = $pdo->prepare($sqlProdotti);
$stm->execute();
$prodotti = $stm->fetchAll(PDO::FETCH_ASSOC);

/* ---- CONTROLLO ORDINE (ROOT O CLIENTE) ---- */
if ($ruolo === 'root') {

    $sqlOrdine = "SELECT * FROM ordini WHERE id = ?";
    $stmOrdine = $pdo->prepare($sqlOrdine);
    $stmOrdine->execute([$idOrdine]);

} else {

    $sqlOrdine = "SELECT * FROM ordini WHERE id = ? AND utente_id = ?";
    $stmOrdine = $pdo->prepare($sqlOrdine);
    $stmOrdine->execute([$idOrdine, $idUtente]);
}
$ordine = $stmOrdine->fetch(PDO::FETCH_ASSOC);

if (!$ordine) {
    die("Ordine non trovato");
}
/* ---- DETTAGLI ORDINE ---- */
$sqlDettagli = "
    SELECT * 
    FROM dettagli_ordine 
    WHERE ordine_id = ?
";

$stmDettagli = $pdo->prepare($sqlDettagli);
$stmDettagli->execute([$idOrdine]);
$detagli = $stmDettagli->fetchAll(PDO::FETCH_ASSOC);

/* ---- SALVATAGGIO MODIFICA ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        /* elimina vecchi dettagli */
        $del = $pdo->prepare("DELETE FROM dettagli_ordine WHERE ordine_id = ?");
        $del->execute([$idOrdine]);
        $prodottiSelezionati = $_POST['prodotto'];
        $quantita = $_POST['quantita'];
        $totale = 0;
        $getPrezzo = $pdo->prepare("SELECT prezzo FROM prodotti WHERE id = ?");
        $insertDettaglio = $pdo->prepare("
            INSERT INTO dettagli_ordine 
            (ordine_id, prodotto_id, quantita, prezzo_unitario)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($prodottiSelezionati as $i => $idProdotto) {
            $qta = (int)$quantita[$i];
            if ($qta <= 0) continue;

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
        $update = $pdo->prepare("
            UPDATE ordini 
            SET totale = ?, stato = 'in elaborazione' 
            WHERE id = ?
        ");
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
body{font-family:Arial;margin:30px;background:#f4f4f4}
.container{background:white;padding:20px}
.box{margin-bottom:15px}
select,input{width:100%;padding:8px;margin-top:5px}
button{padding:10px;background:#333;color:white;border:none;cursor:pointer}
a button{background:red}
</style>

</head>
<body>
<div class="container">
<h1>MODIFICA ORDINE #<?= htmlspecialchars($idOrdine) ?></h1>

<form method="POST">

<?php for ($i = 0; $i < 3; $i++): ?>
    <div class="box">
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
<a href="home.php">
    <button type="button">ANNULLA</button>
</a>
</form>
</div>
    
</body>
</html>

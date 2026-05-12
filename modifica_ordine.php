<?php
session_start();
require 'config.php';

if (!isset($_SESSION['utente_id'], $_SESSION['utente_ruolo'])) {
    die("Accesso negato");
}

$idUtente = $_SESSION['utente_id'];
$ruolo = $_SESSION['utente_ruolo'];
$idOrdine = $_GET['id'] ?? null;

if (!$idOrdine) {
    die("Ordine non valido");
}

/* ---- PRODOTTI ---- */
$stm = $pdo->prepare("
    SELECT id, nome, prezzo
    FROM prodotti
    WHERE disponibile = 1
");
$stm->execute();
$prodotti = $stm->fetchAll(PDO::FETCH_ASSOC);

/* ---- ORDINE ---- */
if ($ruolo === 'root') {

    $stmOrdine = $pdo->prepare("
        SELECT *
        FROM ordini
        WHERE id = ?
    ");
    $stmOrdine->execute([$idOrdine]);

} else {

    $stmOrdine = $pdo->prepare("
        SELECT *
        FROM ordini
        WHERE id = ? AND utente_id = ?
    ");
    $stmOrdine->execute([$idOrdine, $idUtente]);
}

$ordine = $stmOrdine->fetch(PDO::FETCH_ASSOC);

if (!$ordine) {
    die("Ordine non trovato");
}

/* ---- DETTAGLI ORDINE ---- */
$stmDet = $pdo->prepare("
    SELECT *
    FROM dettagli_ordine
    WHERE ordine_id = ?
");
$stmDet->execute([$idOrdine]);
$detagli = $stmDet->fetchAll(PDO::FETCH_ASSOC);

/* ---- UPDATE ORDINE ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $pdo->beginTransaction();

        $pdo->prepare("
            DELETE FROM dettagli_ordine
            WHERE ordine_id = ?
        ")->execute([$idOrdine]);

        $prod = $_POST['prodotto'] ?? [];
        $qta = $_POST['quantita'] ?? [];

        $tot = 0;

        $get = $pdo->prepare("
            SELECT prezzo
            FROM prodotti
            WHERE id = ?
        ");

        $ins = $pdo->prepare("
            INSERT INTO dettagli_ordine
            (ordine_id, prodotto_id, quantita, prezzo_unitario)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($prod as $i => $idProd) {

            $q = (int)($qta[$i] ?? 1);
            if ($q <= 0) continue;

            $get->execute([$idProd]);
            $prezzo = (float)$get->fetchColumn();

            $tot += $prezzo * $q;

            $ins->execute([
                $idOrdine,
                $idProd,
                $q,
                $prezzo
            ]);
        }

        $pdo->prepare("
            UPDATE ordini
            SET totale = ?, stato = 'in elaborazione'
            WHERE id = ?
        ")->execute([$tot, $idOrdine]);

        $pdo->commit();

        header("Location: home.php");
        exit;

    } catch (Exception $e) {

        $pdo->rollBack();
        die($e->getMessage());
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

button{
    padding:10px;
    border:none;
    cursor:pointer;
    font-weight:bold;
}

.save{
    background:#333;
    color:white;
}

.cancel{
    background:red;
    color:white;
    text-decoration:none;
    display:inline-block;
    text-align:center;
    margin-left:10px;
    padding:10px;
}
</style>

</head>

<body>

<div class="container">

<h1>MODIFICA ORDINE #<?= htmlspecialchars($idOrdine) ?></h1>

<form method="POST">

<?php if (count($detagli) > 0): ?>

    <?php foreach ($detagli as $d): ?>

        <div class="box">

            <label>Prodotto</label>

            <select name="prodotto[]">

                <?php foreach ($prodotti as $p): ?>
                    <option value="<?= $p['id'] ?>"
                        <?= $p['id'] == $d['prodotto_id'] ? 'selected' : '' ?>>
                        <?= $p['nome'] ?> - <?= $p['prezzo'] ?> €
                    </option>
                <?php endforeach; ?>

            </select>

            <label>Quantità</label>

            <input type="number"
                   name="quantita[]"
                   min="1"
                   value="<?= $d['quantita'] ?>">

        </div>

    <?php endforeach; ?>

<?php else: ?>

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

<?php endif; ?>

<button type="submit" class="save">SALVA MODIFICHE</button>

<a href="home.php" class="cancel">
    ANNULLA
</a>

</form>

</div>

</body>
</html>

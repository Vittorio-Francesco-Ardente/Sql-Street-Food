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
    ORDER BY nome
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

/* ---- DETTAGLI ---- */
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MODIFICA ORDINE</title>

<style>
body {
    font-family: Arial;
    margin: 30px;
    background: #f4f4f4;
}

.box {
    background: white;
    padding: 20px;
    max-width: 600px;
    margin: auto;
}

.riga {
    border: 1px solid #ddd;
    padding: 10px;
    margin-bottom: 10px;
}

label {
    font-weight: bold;
}

select, input {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    margin-bottom: 10px;
}

button {
    width: 100%;
    padding: 10px;
    border: none;
    cursor: pointer;
    background: #333;
    color: white;
}

button.add {
    background: green;
    margin-bottom: 10px;
}

a {
    display: block;
    margin-top: 15px;
    text-align: center;
    color: #333;
}
</style>

</head>

<body>

<div class="box">

<h1>MODIFICA ORDINE #<?= $idOrdine ?></h1>

<form method="POST">

    <?php if (count($detagli) > 0): ?>

        <?php foreach ($detagli as $d): ?>

            <div class="riga">

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
                <input type="number" name="quantita[]" value="<?= $d['quantita'] ?>" min="1">

            </div>

        <?php endforeach; ?>

    <?php else: ?>

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

    <?php endif; ?>

    <button type="submit">SALVA MODIFICHE</button>
    <button type="button" class="add" onclick="location.href='home.php'">
        ANNULLA
    </button>

</form>

<a href="home.php">Torna alla home</a>

</div>

</body>
</html>

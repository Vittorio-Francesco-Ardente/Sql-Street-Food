<?php
session_start();
require 'config.php';

if (!isset($_SESSION['utente_id'], $_SESSION['utente_ruolo'])) {
    die("Accesso negato");
}

$idUtente = $_SESSION['utente_id'];
$ruolo = strtolower($_SESSION['utente_ruolo']); 

/* ---- MENU ---- */
$menu = $pdo->query("
    SELECT id, nome, descrizione, prezzo
    FROM prodotti
    ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);

/* ---- ORDINI ---- */
if ($ruolo === 'root') {

    $stmt = $pdo->query("
        SELECT id, data_ordine, stato, totale
        FROM ordini
        ORDER BY id DESC
    ");

} else {

    $stmt = $pdo->prepare("
        SELECT id, data_ordine, stato, totale
        FROM ordini
        WHERE utente_id = ?
        ORDER BY id DESC
    ");

    $stmt->execute([$idUtente]);
}

$ordini = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ---- DETTAGLI ORDINI ---- */
$dati = $pdo->query("
    SELECT d.ordine_id, p.nome AS prodotto, d.quantita, d.prezzo_unitario
    FROM dettagli_ordine d
    JOIN prodotti p ON p.id = d.prodotto_id
")->fetchAll(PDO::FETCH_ASSOC);

$dettagli = [];

foreach ($dati as $r) {
    $dettagli[$r['ordine_id']][] = $r;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>HOME</title>

<style>
body{font-family:Arial;margin:30px;background:#f4f4f4}
table{width:100%;border-collapse:collapse;background:white;margin-bottom:20px}
th,td{border:1px solid #ccc;padding:10px}
th{background:#333;color:white}
.box{background:white;padding:15px;margin-bottom:20px}
.btn{padding:10px;background:#333;color:white;text-decoration:none;display:inline-block}
.btn-elimina{background:red;color:white;padding:8px;border:none}
</style>

</head>
<body>

<h1>MENU</h1>

<table>
<tr>
    <th>Nome</th>
    <th>Descrizione</th>
    <th>Prezzo</th>
</tr>

<?php foreach ($menu as $m): ?>
<tr>
    <td><?= $m['nome'] ?></td>
    <td><?= $m['descrizione'] ?></td>
    <td><?= $m['prezzo'] ?> €</td>
</tr>
<?php endforeach; ?>
</table>

<a class="btn" href="nuovo_ordine.php">+ NUOVO ORDINE</a>

<h1>ORDINI</h1>

<?php foreach ($ordini as $o): ?>
<div class="box">

    <h3>Ordine #<?= $o['id'] ?></h3>
    <p><?= $o['data_ordine'] ?> | <?= $o['stato'] ?> | <?= $o['totale'] ?> €</p>

    <?php if ($ruolo === 'root'): ?>
        <a href="elimina_ordine.php?id=<?= $o['id'] ?>"
           onclick="return confirm('Eliminare ordine?')">
            <button class="btn-elimina">ELIMINA</button>
        </a>
    <?php endif; ?>

    <table>
        <tr>
            <th>Prodotto</th>
            <th>Quantità</th>
            <th>Prezzo</th>
        </tr>

        <?php if (!empty($dettagli[$o['id']])): ?>
            <?php foreach ($dettagli[$o['id']] as $d): ?>
                <tr>
                    <td><?= $d['prodotto'] ?></td>
                    <td><?= $d['quantita'] ?></td>
                    <td><?= $d['prezzo_unitario'] ?> €</td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="3">Nessun prodotto</td></tr>
        <?php endif; ?>

    </table>

</div>
<?php endforeach; ?>

</body>
</html>

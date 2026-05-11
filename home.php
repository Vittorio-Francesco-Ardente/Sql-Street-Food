<?php
require 'config.php';
session_start();

/* ---- Viene eseguito un ulteriore controllo all'accesso dell'utente ---- */
if(!isset($_SESSION['id']) || !isset($_SESSION['ruolo']))
{
    die("Accesso negato. Effettua il login.");
}

$idUtente = $_SESSION['id'];
$ruolo = $_SESSION['ruolo'];


$sqlMenu = "SELECT prodotti.id, prodotti.nome, prodotti.descrizione, prodotti.prezzo, prodotti.disponibile, categorie.nome AS categoria
            FROM prodotti
            LEFT JOIN categorie
            ON prodotti.categoria_id = categorie.id
            ORDER BY prodotti.id";

$stmMenu = $pdo->prepare($sqlMenu);
$stmMenu->execute();
$menu = $stmMenu->fetchAll(PDO::FETCH_ASSOC);

if($ruolo == 'root')
{
    $sqlOrdini = "SELECT id, data_ordine, stato, totale
                  FROM ordini
                  ORDER BY id";

    $stmOrdini = $pdo->prepare($sqlOrdini);
    $stmOrdini->execute();
}
else
{
    $sqlOrdini = "SELECT id, data_ordine, stato, totale
                  FROM ordini
                  WHERE utente_id = ?
                  ORDER BY id";

    $stmOrdini = $pdo->prepare($sqlOrdini);
    $stmOrdini->execute([$idUtente]);
}

$ordini = $stmOrdini->fetchAll(PDO::FETCH_ASSOC);

$sqlDettagli = "SELECT dettagli_ordine.ordine_id, prodotti.nome AS prodotto, dettagli_ordine.quantita, dettagli_ordine.prezzo_unitario
                FROM dettagli_ordine
                INNER JOIN prodotti
                ON dettagli_ordine.prodotto_id = prodotti.id";

$stmDettagli = $pdo->prepare($sqlDettagli);
$stmDettagli->execute();

$dettagli = [];
while($riga = $stmDettagli->fetch(PDO::FETCH_ASSOC))
{
    $dettagli[$riga['ordine_id']][] = $riga;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>SQL STREET FOOD</title>
    <style>
        body{
            font-family: Arial;
            margin: 30px;
            background-color: #f4f4f4;
        }
        h1{
            color: #333;
        }
        table{
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
            background-color: white;
        }
        th, td{
            border: 1px solid #ccc;
            padding: 10px;
        }
        th{
            background-color: #333;
            color: white;
        }
        .non-disponibile{
            color: red;
            font-weight: bold;
        }
        .disponibile{
            color: green;
            font-weight: bold;
        }
        .ordine-box{
            background-color: white;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
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
    </style>
</head>
<body>
<h1>MENU'</h1>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>NOME</th>
            <th>DESCRIZIONE</th>
            <th>PREZZO</th>
            <th>CATEGORIA</th>
            <th>DISPONIBILE</th>
        </tr>
    </thead>
    <tbody>

    <?php foreach($menu as $prodotto): ?>
        <tr>
            <td><?= $prodotto['id'] ?></td>
            <td><?= $prodotto['nome'] ?></td>
            <td><?= $prodotto['descrizione'] ?></td>
            <td><?= $prodotto['prezzo'] ?> €</td>
            <td><?= $prodotto['categoria'] ?></td>

            <?php if($prodotto['disponibile']): ?>
                <td class="disponibile">SI</td>
            <?php else: ?>
                <td class="non-disponibile">NO</td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h1>ORDINI CLIENTI</h1>

<a href="nuovo_ordine.php">
    <button>NUOVO ORDINE</button>
</a>

<br><br>

<?php foreach($ordini as $ordine): ?>
<div class="ordine-box">
    <h3>Ordine #<?= $ordine['id'] ?></h3>
    <p><strong>Data:</strong> <?= $ordine['data_ordine'] ?></p>
    <p><strong>Stato:</strong> <?= $ordine['stato'] ?></p>
    <p><strong>Totale:</strong> <?= $ordine['totale'] ?> €</p>
    <a href="modifica_ordine.php?id=<?= $ordine['id'] ?>">
        <button>MODIFICA ORDINE</button>
    </a>
    <br><br><br>
    <table>
        <thead>
            <tr>
                <th>PRODOTTO</th>
                <th>QUANTITA'</th>
                <th>PREZZO UNITARIO</th>
            </tr>
        </thead>
        <tbody>
        <?php if(isset($dettagli[$ordine['id']])): ?>
            <?php foreach($dettagli[$ordine['id']] as $d): ?>
                <tr>
                    <td><?= $d['prodotto'] ?></td>
                    <td><?= $d['quantita'] ?></td>
                    <td><?= $d['prezzo_unitario'] ?> €</td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3">Nessun dettaglio disponibile</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endforeach; ?>
</body>
</html>

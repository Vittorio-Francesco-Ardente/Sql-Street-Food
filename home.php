<?php
require 'config.php';

$sqlMenu = "SELECT prodotti.id,
                   prodotti.nome,
                   prodotti.descrizione,
                   prodotti.prezzo,
                   prodotti.disponibile,
                   categorie.nome AS categoria
            FROM prodotti
            LEFT JOIN categorie
            ON prodotti.categoria_id = categorie.id
            ORDER BY prodotti.id";

$stmMenu = $pdo->prepare($sqlMenu);
$stmMenu->execute();
$menu = $stmMenu->fetchAll(PDO::FETCH_ASSOC);

$sqlOrdini = "SELECT ordini.id,
                     ordini.data_ordine,
                     ordini.stato,
                     ordini.totale
              FROM ordini
              ORDER BY ordini.id";

$stmOrdini = $pdo->prepare($sqlOrdini);
$stmOrdini->execute();
$ordini = $stmOrdini->fetchAll(PDO::FETCH_ASSOC);

$sqlDettagli = "SELECT dettagli_ordine.ordine_id,
                       prodotti.nome AS prodotto,
                       dettagli_ordine.quantita,
                       dettagli_ordine.prezzo_unitario
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOME RISTORANTE</title>

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
            text-align: left;
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

            <?php
            foreach($menu as $prodotto)
            {
                echo "<tr>";
                echo "<td>".$prodotto['id']."</td>";
                echo "<td>".$prodotto['nome']."</td>";
                echo "<td>".$prodotto['descrizione']."</td>";
                echo "<td>".$prodotto['prezzo']." €</td>";
                echo "<td>".$prodotto['categoria']."</td>";

                if($prodotto['disponibile'])
                {
                    echo "<td class='disponibile'>SI</td>";
                }
                else
                {
                    echo "<td class='non-disponibile'>NO</td>";
                }
                echo "</tr>";
            }
            ?>

        </tbody>
    </table>
    <h1>ORDINI CLIENTI</h1>
    <a href="nuovo_ordine.php">
        <button>NUOVO ORDINE</button>
    </a>
    <br><br>

    <?php
    foreach($ordini as $ordine)
    {
        echo "<div class='ordine-box'>";
        echo "<h3>Ordine #".$ordine['id']."</h3>";
        echo "<p><strong>Data:</strong> ".$ordine['data_ordine']."</p>";
        echo "<p><strong>Stato:</strong> ".$ordine['stato']."</p>";
        echo "<p><strong>Totale:</strong> ".$ordine['totale']." €</p>";
        echo "<a href='modifica_ordine.php?id=".$ordine['id']."'>
                <button>MODIFICA ORDINE</button>
              </a>";
        echo "<br><br>";
        echo "<table>";
        echo "<thead>
                <tr>
                    <th>PRODOTTO</th>
                    <th>QUANTITA'</th>
                    <th>PREZZO UNITARIO</th>
                </tr>
              </thead>";
        echo "<tbody>";

        if(isset($dettagli[$ordine['id']]))
        {
            foreach($dettagli[$ordine['id']] as $d)
            {
                echo "<tr>";
                echo "<td>".$d['prodotto']."</td>";
                echo "<td>".$d['quantita']."</td>";
                echo "<td>".$d['prezzo_unitario']." €</td>";
                echo "</tr>";
            }
        }
        else
        {
            echo "<tr>";
            echo "<td colspan='3'>Nessun dettaglio disponibile</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
    }
    ?>
</body>
</html>

<?php
require 'config.php';

$errore = "";
$successo = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $conferma = trim($_POST['password-confirm'] ?? '');

    // RUOLO SELEZIONATO
    $ruolo = $_POST['ruolo'] ?? 'cliente';

    // sicurezza base
    if (!in_array($ruolo, ['cliente', 'root'])) {
        $ruolo = 'cliente';
    }

    if (
        empty($nome) ||
        empty($email) ||
        empty($password) ||
        empty($conferma)
    ) {
        $errore = "Compila tutti i campi";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errore = "Email non valida";

    } elseif (strlen($password) < 6) {

        $errore = "Password minimo 6 caratteri";

    } elseif ($password !== $conferma) {

        $errore = "Le password non coincidono";

    } else {

        try {

            // controllo email esistente
            $check = $pdo->prepare("
                SELECT id
                FROM utenti
                WHERE email = :email
            ");

            $check->execute([
                ':email' => $email
            ]);

            if ($check->fetch()) {

                $errore = "Email già registrata";

            } else {

                $pdo->beginTransaction();

                $hash = password_hash($password, PASSWORD_BCRYPT);

                $insert = $pdo->prepare("
                    INSERT INTO utenti(
                        nome,
                        email,
                        password,
                        ruolo
                    )
                    VALUES(
                        :nome,
                        :email,
                        :password,
                        :ruolo
                    )
                ");

                $insert->execute([
                    ':nome' => $nome,
                    ':email' => $email,
                    ':password' => $hash,
                    ':ruolo' => $ruolo
                ]);

                $pdo->commit();

                $successo = "Registrazione completata";
            }

        } catch (PDOException $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errore = "Errore registrazione: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registrazione</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="background-grid"></div>

<div class="container">

    <form class="card" method="POST">

        <h1>Registrazione</h1>

        <?php if ($errore): ?>
            <div class="message error">
                <?php echo $errore; ?>
            </div>
        <?php endif; ?>

        <?php if ($successo): ?>
            <div class="message success">
                <?php echo $successo; ?>
            </div>
        <?php endif; ?>

        <div class="input-group">
            <label>Nome</label>
            <input type="text" name="nome" required>
        </div>

        <div class="input-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <div class="input-group">
            <label>Conferma Password</label>
            <input type="password" name="password-confirm" required>
        </div>

        <!-- RUOLO -->
        <div class="input-group">
            <label>Ruolo</label>
            <select name="ruolo" required>
                <option value="cliente">Cliente</option>
                <option value="root">Root</option>
            </select>
        </div>

        <button type="submit">Registrati</button>

        <div class="bottom-link">
            Hai già un account?
            <a href="login.php">Accedi</a>
        </div>

    </form>

</div>

</body>
</html>

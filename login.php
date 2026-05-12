<?php
require 'config.php';

$errore = "";

if($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if(empty($email) || empty($password)) {

        $errore = "Compila tutti i campi";

    } else {

        $query = $pdo->prepare("
            SELECT *
            FROM utenti
            WHERE email = :email
        ");

        $query->execute([
            ':email' => $email
        ]);

        $utente = $query->fetch();

        if(
            $utente &&
            password_verify($password, $utente['password'])
        ) {

            session_regenerate_id(true);

            $_SESSION['utente_id'] = $utente['id'];
            $_SESSION['utente_nome'] = $utente['nome'];
            $_SESSION['utente_ruolo'] = $utente['ruolo'];

            header("Location: index.php");
            exit;

        } else {

            $errore = "Credenziali non valide";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="background-grid"></div>

<div class="container">

    <form class="card" method="POST">

        <h1>Login</h1>

        <?php if($errore): ?>

            <div class="message error">
                <?php echo $errore; ?>
            </div>

        <?php endif; ?>

        <div class="input-group">

            <label>Email</label>

            <input type="email" name="email" required>

        </div>

        <div class="input-group">

            <label>Password</label>

            <input type="password" name="password" required>

        </div>

        <button type="submit">Accedi</button>

        <div class="bottom-link">

            Non hai un account?

            <a href="registrazione.php">Registrati</a>

        </div>

    </form>

</div>

</body>
</html>

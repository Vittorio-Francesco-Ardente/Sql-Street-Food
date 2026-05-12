<?php
session_start();
require 'config.php';
$errore = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("
        SELECT id, nome, password, ruolo
        FROM utenti
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $utente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($utente && password_verify($password, $utente['password'])) {
        session_regenerate_id(true);
        $_SESSION['utente_id'] = $utente['id'];
        $_SESSION['utente_nome'] = $utente['nome'];
        $_SESSION['utente_ruolo'] = $utente['ruolo'];
        header("Location: home.php");
        exit;

    } else {
        $errore = "Credenziali non valide";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Login</title>
</head>
<body>

<h1>Login</h1>
<?php if ($errore): ?>
<p style="color:red"><?= $errore ?></p>
<?php endif; ?>

<form method="POST">
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Accedi</button>
</form>
</body>
</html>

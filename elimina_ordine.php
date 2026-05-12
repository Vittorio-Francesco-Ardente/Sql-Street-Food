<?php
require 'config.php';
session_start();

if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'root')
{
    die("Accesso negato");
}

if (isset($_GET['id']))
{
    try
    {
        $pdo->exec("SET SESSION innodb_lock_wait_timeout = 10");
        $pdo->exec("SET autocommit = 0");
        $pdo->exec("BEGIN WORK");

        $id = $_GET['id'];
        $sql = "DELETE FROM ordini WHERE id = ?";
        $stm = $pdo->prepare($sql);
        $stm->execute([$id]);
        $pdo->exec("COMMIT");
    }
    catch (Exception $e)
    {
        $pdo->exec("ROLLBACK");

        die("Errore: " . $e->getMessage());
    }
}

header("Location: home.php");
exit();

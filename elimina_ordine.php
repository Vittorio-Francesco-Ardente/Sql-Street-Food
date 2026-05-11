<?php
require 'config.php';
session_start();

if(!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] != 'root')
{
    die("Accesso negato");
}

if(isset($_GET['id']))
{
    $id = $_GET['id'];
    $sql = "DELETE FROM ordini WHERE id = ?";
    $stm = $pdo->prepare($sql);
    $stm->execute([$id]);
}

header("Location: home.php");
exit();

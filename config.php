<?php
try 
{
    $pdo = new PDO("mysql:host=localhost;dbname=street_food;charset=utf8", "root","");
}
catch (PDOException $e) 
{
    echo $e->getMessage();
}
?>
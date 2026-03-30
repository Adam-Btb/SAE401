<?php
if (defined("CHARGE_BD")) {
    $db = "sae401";
    $host = "localhost";
    $user = "root";
    $pwd = "";
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

    try {
        $cnx = new PDO($dsn, $user, $pwd, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e) {
        die('Erreur DB : ' . $e->getMessage());
    }
} else {
    die("Accès interdit");
}
?>
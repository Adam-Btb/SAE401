<?php
define("CHARGE_BD", true);
require 'inc/bd.inc.php';
$req = $cnx->query("SELECT COUNT(*) AS total FROM publications");
$count = $req->fetch();
echo "Nombre de publications : " . $count['total'] . "<br>";

$req = $cnx->query("SELECT * FROM publications ORDER BY id DESC LIMIT 5");
$pubs = $req->fetchAll();
echo "<pre>";
print_r($pubs);
echo "</pre>";
?>

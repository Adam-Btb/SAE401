<?php
require 'inc/bd.inc.php';
$req = $cnx->query("SELECT * FROM publications ORDER BY id DESC LIMIT 5");
var_dump($req->fetchAll());
?>

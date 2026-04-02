<?php
$file = __DIR__ . "/img/uploads/publications/test.txt";
$ok = file_put_contents($file, "test");
echo "Written: " . ($ok !== false ? "YES" : "NO") . "<br>";
echo "cwd: " . getcwd() . "<br>";
?>

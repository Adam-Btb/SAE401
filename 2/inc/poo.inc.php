<?php
spl_autoload_register(function ($class) {
    $file = 'class/' . $class . '.class.php';
    if (file_exists($file)) {
        include $file;
    }
});

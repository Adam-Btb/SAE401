<?php
$content = file_get_contents('class/Vues.class.php');
$content = preg_replace('/(\$photo\s*=\s*\$([a-zA-Z0-9_]+)\[\'photo_profil\'\]\s*\?:\s*\'default\.png\';)/', '$photo = $this->getPhotoProfil($$2[\'photo_profil\'] ?? null);', $content);

$helper = "
    private function getPhotoProfil(\$photo) {
        \$p = \$photo ?: 'default.webp';
        if (\$p !== 'default.webp' && !file_exists(__DIR__ . '/../img/avatars/' . \$p)) {
            return 'default.webp';
        }
        return \$p;
    }
";

if (strpos($content, 'getPhotoProfil') === false) {
    $content = preg_replace('/(class Vues\s*\{)/', "$1" . $helper, $content);
    file_put_contents('class/Vues.class.php', $content);
    echo "Fixed Vues.class.php\n";
} else {
    echo "Already fixed\n";
}

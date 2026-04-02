<?php
/**
 * setup.php — Diagnostic ET correction automatique des dossiers d'upload
 * Ouvrir UNE SEULE FOIS sur le serveur, puis supprimer ce fichier.
 */

$dirs = [
    __DIR__ . '/img/avatars',
    __DIR__ . '/img/uploads/publications',
    __DIR__ . '/img/uploads/messages',
];

echo '<style>body{font-family:sans-serif;padding:20px;background:#111;color:#eee;}
.ok{color:#4caf50;font-weight:bold;} .err{color:#f44336;font-weight:bold;} .warn{color:#ff9800;font-weight:bold;}
pre{background:#1e1e1e;padding:12px;border-radius:6px;overflow:auto;font-size:13px;}
h2{border-bottom:1px solid #333;padding-bottom:8px;margin-top:30px;}
li{margin:6px 0;line-height:1.8;}
code{background:#222;padding:2px 6px;border-radius:4px;}
</style>';

echo '<h1>🔧 Diagnostic & Fix Uploads — StudioChroma</h1>';

// ── 1. Dossiers : création + chmod automatique ───────────────
echo '<h2>📁 Dossiers d\'upload</h2><ul>';
foreach ($dirs as $dir) {
    $rel = str_replace(__DIR__, '', $dir);

    // Création si absent
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    // Tentative de chmod 777
    @chmod($dir, 0777);

    // Test écriture réel
    $test = $dir . '/.write_test';
    $writable = @file_put_contents($test, 'ok') !== false;
    if ($writable) @unlink($test);

    if ($writable) {
        echo '<li><span class="ok">✔ OK — accessible en écriture</span> <code>' . $rel . '</code></li>';
    } else {
        echo '<li><span class="err">✘ TOUJOURS non accessible en écriture</code> <code>' . $rel . '</code><br>';
        echo '<small style="color:#aaa">→ Va dans ton gestionnaire FTP / cPanel, clique-droit sur ce dossier → Modifier les permissions → 755 ou 777</small></span></li>';
    }
}
echo '</ul>';

// ── 2. Config PHP ─────────────────────────────────────────────
echo '<h2>⚙️ Configuration PHP</h2><pre>';
$settings = [
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size'       => ini_get('post_max_size'),
    'max_execution_time'  => ini_get('max_execution_time') . 's',
    'memory_limit'        => ini_get('memory_limit'),
    'file_uploads'        => ini_get('file_uploads') ? 'ON ✔' : 'OFF ✘',
    'upload_tmp_dir'      => ini_get('upload_tmp_dir') ?: sys_get_temp_dir(),
];
foreach ($settings as $k => $v) {
    printf("%-25s %s\n", $k, $v);
}
echo '</pre>';

$tmp = ini_get('upload_tmp_dir') ?: sys_get_temp_dir();
echo '<p>Répertoire temporaire : <code>' . $tmp . '</code> — ';
echo is_writable($tmp) ? '<span class="ok">✔ accessible</span>' : '<span class="err">✘ NON accessible !</span>';
echo '</p>';

// ── 3. Avatar par défaut ──────────────────────────────────────
echo '<h2>🖼️ Avatar par défaut</h2>';
$def = __DIR__ . '/img/avatars/default.webp';
if (file_exists($def)) {
    $size = round(filesize($def) / 1024 / 1024, 2);
    echo '<p><code>img/avatars/default.webp</code> : <strong>' . $size . ' Mo</strong> ';
    if ($size > 0.5) {
        echo '<span class="warn">⚠️ Trop lourde ! Remplace-la par une image &lt; 100 Ko</span>';
    } else {
        echo '<span class="ok">✔ Taille correcte</span>';
    }
    echo '</p>';
} else {
    echo '<p class="err">✘ default.webp manquant dans img/avatars/ !</p>';
}

// ── 4. Bilan fichiers ─────────────────────────────────────────
echo '<h2>📷 Fichiers uploadés</h2>';
foreach ([
    'img/avatars'              => __DIR__ . '/img/avatars',
    'img/uploads/publications' => __DIR__ . '/img/uploads/publications',
    'img/uploads/messages'     => __DIR__ . '/img/uploads/messages',
] as $label => $path) {
    $files = is_dir($path) ? array_diff(scandir($path), ['.', '..', '.gitkeep', '.write_test']) : [];
    echo "<p><strong>$label</strong> : " . count($files) . " fichier(s)";
    if (!empty($files)) {
        echo ' <small style="color:#aaa">(' . implode(', ', array_slice(array_values($files), 0, 5)) . ')</small>';
    }
    echo '</p>';
}

echo '<hr><p class="warn">⚠️ Supprime ce fichier (<code>setup.php</code>) après vérification !</p>';
echo '<p style="color:#aaa;font-size:13px;">Tu n\'as besoin d\'ouvrir cette page qu\'<strong>une seule fois</strong> après le premier déploiement.</p>';

<?php

$appComponents = [];
$dirs = scandir('components/Application');
foreach ($dirs as $dir) {
    if ($dir === '.' || $dir === '..' || ! is_dir("components/Application/$dir")) {
        continue;
    }
    $appComponents[] = $dir;
}

$files = [];
$dirs = ['components', 'framework', 'tests'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $out = trim(shell_exec("find $dir -type f -name \"*.php\""));
        if ($out) {
            $files = array_merge($files, explode("\n", $out));
        }
    }
}

$count = 0;
foreach ($files as $file) {
    if (! $file) {
        continue;
    }
    $content = file_get_contents($file);
    $changed = false;

    // 1. Fix missing 'Application' in suite namespaces for ALL components found in Application/
    foreach ($appComponents as $comp) {
        $old = "Avax\\Components\\$comp";
        $new = "Avax\\Components\\Application\\$comp";
        if (str_contains($content, $old) && ! str_contains($content, $new)) {
            $content = str_replace($old, $new, $content);
            $changed = true;
        }
    }

    // 2. Fix 'Components\ystem' typo in framework
    $typo = 'Avax\\Components\\ystem';
    $fixed = 'Avax\\Framework\\System';
    if (str_contains($content, $typo)) {
        $content = str_replace($typo, $fixed, $content);
        $changed = true;
    }

    if ($changed) {
        file_put_contents($file, $content);
        echo "Fixed $file\n";
        $count++;
    }
}

echo "Fixed $count files/references.\n";

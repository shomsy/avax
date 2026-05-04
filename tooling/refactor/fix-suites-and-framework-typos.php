<?php

$suites = [
    'Text',
    'DateTime',
    'Config',
    'Filesystem',
    'Validation',
];

$files = [];
$dirs = ['components/Application', 'framework'];
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
    if (!$file) continue;
    $content = file_get_contents($file);
    $changed = false;

    // 1. Fix missing 'Application' in suite namespaces
    foreach ($suites as $suite) {
        $old = "Avax\\Components\\$suite";
        $new = "Avax\\Components\\Application\\$suite";
        if (str_contains($content, $old) && !str_contains($content, $new)) {
             $content = str_replace($old, $new, $content);
             $changed = true;
        }
    }

    // 2. Fix 'Components\ystem' typo in framework
    $typo = "Avax\\Components\\ystem";
    $fixed = "Avax\\Framework\\System";
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

// Also fix references in ALL php files
$allFiles = [];
$allDirs = ['components', 'framework', 'tests'];
foreach ($allDirs as $dir) {
    if (is_dir($dir)) {
        $out = trim(shell_exec("find $dir -type f -name \"*.php\""));
        if ($out) {
            $allFiles = array_merge($allFiles, explode("\n", $out));
        }
    }
}

foreach ($allFiles as $file) {
    if (!$file) continue;
    $content = file_get_contents($file);
    $changed = false;

    foreach ($suites as $suite) {
        $old = "Avax\\Components\\$suite";
        $new = "Avax\\Components\\Application\\$suite";
        if (str_contains($content, $old) && !str_contains($content, $new)) {
             $content = str_replace($old, $new, $content);
             $changed = true;
        }
    }
    
    $typo = "Avax\\Components\\ystem";
    $fixed = "Avax\\Framework\\System";
    if (str_contains($content, $typo)) {
        $content = str_replace($typo, $fixed, $content);
        $changed = true;
    }

    if ($changed) {
        file_put_contents($file, $content);
        echo "Fixed reference in $file\n";
        $count++;
    }
}

echo "Fixed $count files/references.\n";

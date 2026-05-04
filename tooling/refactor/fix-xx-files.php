<?php

$dirs = ['components', 'tests', 'framework'];
$files = [];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $out = trim(shell_exec("find $dir -type f -name \"XX*\""));
        if ($out) {
            $files = array_merge($files, explode("\n", $out));
        }
    }
}

foreach ($files as $file) {
    if (!$file) continue;
    $content = file_get_contents($file);
    if (preg_match('/(?:class|interface|enum|trait)\s+([a-zA-Z0-9_]+)/', $content, $m)) {
        $name = $m[1];
        $dir = dirname($file);
        $newName = $dir . '/' . $name . '.php';
        echo "Renaming $file to $newName\n";
        rename($file, $newName);
    } else {
        echo "Could not find class name in $file\n";
    }
}

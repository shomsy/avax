<?php

$classToFind = $argv[1];
$backupFile = __DIR__.'/../avax.txt';
$content = file_get_contents($backupFile);

// e.g. class WorkerProcess
$pattern = '/(?:final\s+|abstract\s+|readonly\s+)*class\s+'.$classToFind.'\b[^{]*\{.*?(?=\n(?:final\s+|abstract\s+|readonly\s+)*(?:class|interface|trait|enum)\s|\z)/s';
if (preg_match($pattern, $content, $matches)) {
    echo $matches[0];
} else {
    echo "Not found.\n";
}

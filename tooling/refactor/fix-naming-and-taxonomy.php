<?php

$dirs = ['components', 'tests', 'framework'];
$files = [];
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
    
    // 1. TransactionManager -> Transactions
    if (str_contains($content, 'TransactionManager')) {
        // Specifically for the DataStack\Database namespace
        if (str_contains($content, 'Avax\Components\DataStack\Database\System\Capabilities\Transactions')) {
             $content = str_replace('TransactionManager', 'Transactions', $content);
             $changed = true;
        }
    }

    // 2. Resolution double-folder fix
    $oldRes = 'Avax\Components\Application\Container\System\Capabilities\Resolution\Resolution';
    $newRes = 'Avax\Components\Application\Container\System\Capabilities\Resolution';
    if (str_contains($content, $oldRes)) {
        $content = str_replace($oldRes, $newRes, $content);
        $changed = true;
    }

    // 3. Telemetry Support -> Trackers
    $oldTel = 'Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Support';
    $newTel = 'Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Trackers';
    if (str_contains($content, $oldTel)) {
        $content = str_replace($oldTel, $newTel, $content);
        $changed = true;
    }

    if ($changed) {
        file_put_contents($file, $content);
        echo "Fixed namespaces in $file\n";
        $count++;
    }
}
echo "Fixed $count files.\n";

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
    
    // Fix legacy LocalDisk to canonical
    if (str_contains($content, 'Avax\Filesystem\Disks\Local\LocalDisk')) {
        $content = str_replace(
            'Avax\Filesystem\Disks\Local\LocalDisk', 
            'Avax\Components\Application\Filesystem\System\Capabilities\Disks\Local\LocalDisk', 
            $content
        );
        $changed = true;
    }
    
    // Fix Disk reference (missing System\Capabilities)
    if (str_contains($content, 'Avax\Components\Application\Filesystem\Disks\Disk')) {
        $content = str_replace(
            'Avax\Components\Application\Filesystem\Disks\Disk', 
            'Avax\Components\Application\Filesystem\System\Capabilities\Disks\Disk', 
            $content
        );
        $changed = true;
    }

    if ($changed) {
        file_put_contents($file, $content);
        echo "Fixed namespace in $file\n";
        $count++;
    }
}
echo "Fixed $count files.\n";

<?php

declare(strict_types=1);
$dirs = ['components/Data', 'components/Database', 'components/Persistence'];
foreach ($dirs as $dir) {
    echo sprintf('Checking dir: %s%s', $dir, PHP_EOL);
    if (! is_dir($dir)) {
        echo sprintf('Dir not found: %s%s', $dir, PHP_EOL);

        continue;
    }

    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($files as $file) {
        if ($file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            $newContent = str_replace('DataStack\\', '', $content);
            if ($content !== $newContent) {
                file_put_contents($file->getPathname(), $newContent);
                echo 'Updated: ' . $file->getPathname() . "\n";
            } else {
                echo 'No change needed for: ' . $file->getPathname() . "\n";
            }
        }
    }
}

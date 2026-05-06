<?php

declare(strict_types=1);

$directories = ['components', 'framework'];
$technicalFolders = [
    'contracts', 'interfaces', 'exceptions', 'exception', 'enums', 'traits', 'types', 'abstracts', 'base',
];

$violations = [];

foreach ($directories as $dir) {
    if (! is_dir($dir)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isDir()) {
            $name = strtolower($file->getFilename());
            if ($name === '.' || $name === '..') {
                continue;
            }

            if (in_array($name, $technicalFolders)) {
                $violations[] = "[TECHNICAL FOLDER: $name] ".$file->getPathname();
            }
        }
    }
}

echo "=== TECHNICAL FOLDER AUDIT ===\n\n";

if (! empty($violations)) {
    foreach (array_unique($violations) as $v) {
        echo "- $v\n";
    }
    echo "\nTotal Suspicious Folders: ".count(array_unique($violations))."\n";
} else {
    echo "✨ No generic technical folders found!\n";
}

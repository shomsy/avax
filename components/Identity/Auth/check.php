<?php

declare(strict_types=1);
$baseDir       = '/home/shomsy/projects/components/Foundation/Auth/System';
$baseNamespace = 'Avax\\Auth\\System';

$iterator   = new RecursiveIteratorIterator(iterator: new RecursiveDirectoryIterator(directory: $baseDir));
$mismatches = [];

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents(filename: $file->getPathname());
        if (preg_match(pattern: '/namespace\s+([^;]+);/', subject: $content, matches: $matches)) {
            $actualNamespace = $matches[1];

            $relativePath      = str_replace(search: $baseDir, replace: '', subject: $file->getPathname());
            $expectedNamespace = $baseNamespace . str_replace(search: '/', replace: '\\', subject: dirname(path: $relativePath));
            $expectedNamespace = rtrim(string: $expectedNamespace, characters: '\\'); // handle root directory

            if ($actualNamespace !== $expectedNamespace) {
                $mismatches[] = [
                    'file'     => $file->getPathname(),
                    'actual'   => $actualNamespace,
                    'expected' => $expectedNamespace,
                ];
            }
        }
    }
}

file_put_contents(filename: '/home/shomsy/projects/components/Foundation/Auth/mismatches.json', data: json_encode(value: $mismatches, flags: JSON_PRETTY_PRINT));
echo "Done.\n";

<?php
$baseDir       = '/home/shomsy/projects/components/Foundation/Auth/System';
$baseNamespace = 'Avax\\Auth\\System';

$iterator   = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
$mismatches = [];

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            $actualNamespace = $matches[1];

            $relativePath      = str_replace($baseDir, '', $file->getPathname());
            $expectedNamespace = $baseNamespace . str_replace('/', '\\', dirname($relativePath));
            $expectedNamespace = rtrim($expectedNamespace, '\\'); // handle root directory

            if ($actualNamespace !== $expectedNamespace) {
                $mismatches[] = [
                    'file'     => $file->getPathname(),
                    'actual'   => $actualNamespace,
                    'expected' => $expectedNamespace
                ];
            }
        }
    }
}

file_put_contents('/home/shomsy/projects/components/Foundation/Auth/mismatches.json', json_encode($mismatches, JSON_PRETTY_PRINT));
echo "Done.\n";

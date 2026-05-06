<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class CheckNamespaceDrift
{
    private array $forbiddenNamespaces = [
        // Lowercase components namespace forbidden in canonical code
        'components\\',
        // Legacy namespaces forbidden except in bridges
        'Avax\\Session\\',
        'Avax\\Middleware\\',
        'Avax\\Database\\System\\',
    ];

    private array $checkedFiles = [];

    public function check(): array
    {
        $this->scanForNamespaceDrift();

        return [
            'status' => $this->checkedFiles === [] ? 'PASS' : 'FAIL',
            'files' => $this->checkedFiles,
        ];
    }

    private function scanForNamespaceDrift(): void
    {
        $basePath = dirname(__DIR__, 2);
        $this->checkDirectory($basePath.'/components');
        $this->checkDirectory($basePath.'/framework');
    }

    private function checkDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
            RecursiveIteratorIterator::SELF_FIRST,
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $filePath = $file->getPathname();
            // Skip allowed bridge files
            if (str_contains($filePath, '/DataFoundation/')) {
                continue;
            }

            if (str_contains($filePath, '/DataLayer/')) {
                continue;
            }

            $content = file_get_contents($filePath);
            foreach ($this->forbiddenNamespaces as $forbiddenNamespace) {
                if (str_contains($content, 'namespace '.$forbiddenNamespace)) {
                    $this->checkedFiles[] = $filePath.': contains '.$forbiddenNamespace;
                }
            }
        }
    }
}

if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    $checker = new CheckNamespaceDrift();
    $result = $checker->check();

    echo $result['status']."\n";

    if (! empty($result['files'])) {
        echo implode("\n", $result['files'])."\n";
        exit(1);
    }

    exit(0);
}

<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class CheckPublicSurface
{
    private array $errors = [];

    public function check() : array
    {
        $this->checkPublicSurfaceClassesAreThin();

        return [
            'status' => empty($this->errors) ? 'PASS' : 'FAIL',
            'errors' => $this->errors,
        ];
    }

    private function checkPublicSurfaceClassesAreThin() : void
    {
        $componentsPath = dirname(__DIR__, 2) . '/components';

        $this->scanPublicSurface($componentsPath);
    }

    private function scanPublicSurface(string $path) : void
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

            if (! str_contains($file->getPathname(), '/PublicSurface/')) {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            // Check for heavy behavior - only flag if excessive private state
            $privateCount = preg_match_all('/private\s+\w+\s+\$\w+\s*=/', $content);
            if ($privateCount > 3) {
                $this->errors[] = $file->getPathname() . ': PublicSurface has excessive private state (' . $privateCount . ' properties)';
            }
        }
    }
}

if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    $checker = new CheckPublicSurface();
    $result = $checker->check();

    echo $result['status'] . "\n";

    if (! empty($result['errors'])) {
        echo implode("\n", $result['errors']) . "\n";
        exit(1);
    }

    exit(0);
}

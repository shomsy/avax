<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class CheckRuntimeLeaks
{
    private array $forbiddenRuntimeImports
        = [
            'Avax\System\Capabilities\Runtime\Adapters',
        ];

    private array $errors = [];

    public function check(): array
    {
        $this->checkNoRuntimeLeaksOutsideFramework();

        return [
            'status' => $this->errors === [] ? 'PASS' : 'FAIL',
            'errors' => $this->errors,
        ];
    }

    private function checkNoRuntimeLeaksOutsideFramework(): void
    {
        $componentsPath = dirname(__DIR__, 2) . '/components';

        if (!is_dir($componentsPath)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($componentsPath),
            RecursiveIteratorIterator::SELF_FIRST,
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            foreach ($this->forbiddenRuntimeImports as $forbiddenRuntimeImport) {
                if (str_contains($content, 'use ' . $forbiddenRuntimeImport)) {
                    $this->errors[] = $file->getPathname() . ': imports runtime adapter outside framework/System';
                }
            }
        }
    }
}

if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    $checker = new CheckRuntimeLeaks();
    $result = $checker->check();

    echo $result['status'] . "\n";

    if (!empty($result['errors'])) {
        echo implode("\n", $result['errors']) . "\n";
        exit(1);
    }

    exit(0);
}

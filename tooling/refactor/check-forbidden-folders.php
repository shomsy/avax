<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

final class CheckForbiddenFolders
{
    private array $forbiddenFolders = [
        'System',
        'DI',
        'ServerRequest',
        'Auth',
        'Providers',
        'Traits',
        'Writers',
        'Config',
        'Presentation',
        'bootstrap',
    ];

    private array $errors = [];

    public function check(): array
    {
        $this->checkNoForbiddenFoldersAtRoot();

        return [
            'status' => $this->errors === [] ? 'PASS' : 'FAIL',
            'errors' => $this->errors,
        ];
    }

    private function checkNoForbiddenFoldersAtRoot(): void
    {
        $basePath = dirname(__DIR__, 2);

        foreach ($this->forbiddenFolders as $forbiddenFolder) {
            $path = $basePath . '/' . $forbiddenFolder;
            if (is_dir($path)) {
                $this->errors[] = sprintf('Forbidden folder at repo root: %s/', $forbiddenFolder);
            }
        }
    }
}

if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    $checker = new CheckForbiddenFolders();
    $result  = $checker->check();

    echo $result['status'] . "\n";

    if (! empty($result['errors'])) {
        echo implode("\n", $result['errors']) . "\n";
        exit(1);
    }

    exit(0);
}

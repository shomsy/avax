<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

final class CheckDuplicateOwners
{
    private array $forbiddenOwners
        = [
            'Session',
            'Middleware',
            'Commands',
        ];

    private array $allowedBridges
        = [
            'DataFoundation', // Legacy bridge - behavior moved to DataStack/Data
            'DataLayer',     // Legacy bridge - behavior moved to DataStack/Persistence
        ];

    private array $errors = [];

    public function check(): array
    {
        $this->checkNoForbiddenOwnersAtRoot();
        $this->checkDuplicateBehaviorMerged();

        return [
            'status' => $this->errors === [] ? 'PASS' : 'FAIL',
            'errors' => $this->errors,
        ];
    }

    private function checkNoForbiddenOwnersAtRoot(): void
    {
        $componentsPath = dirname(__DIR__, 2) . '/components';

        foreach ($this->forbiddenOwners as $forbiddenOwner) {
            $path = $componentsPath . '/' . $forbiddenOwner;
            if (is_dir($path)) {
                $this->errors[] = 'Forbidden root owner at components/' . $forbiddenOwner;
            }
        }

        // DataFoundation and DataLayer are allowed as bridges
        foreach ($this->allowedBridges as $allowedBridge) {
            $path = $componentsPath . '/' . $allowedBridge;
            if (is_dir($path)) {
                // Check if it's a proper bridge (thin) or has real behavior
                $systemPath = $path . '/System';
                if (is_dir($systemPath)) {
                    // Has System - treat as potential duplicate
                    $files = glob($systemPath . '/**/*.php') ?: [];
                    if (count($files) > 5) {
                        $this->errors[] = sprintf('Bridge %s has too much real behavior', $allowedBridge);
                    }
                }
            }
        }
    }

    private function checkDuplicateBehaviorMerged(): void
    {
        // Check Session is not duplicated
        if (is_dir(dirname(__DIR__, 2) . '/components/Session') && !is_dir(dirname(__DIR__, 2) . '/components/HTTP/Session')) {
            $this->errors[] = 'components/Session not moved to components/HTTP/Session';
        }

        // Check Middleware is not duplicated
        if (is_dir(dirname(__DIR__, 2) . '/components/Middleware') && !is_dir(dirname(__DIR__, 2) . '/components/HTTP/Middleware')) {
            $this->errors[] = 'components/Middleware not moved to components/HTTP/Middleware';
        }
    }
}

if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    $checker = new CheckDuplicateOwners();
    $result = $checker->check();

    echo $result['status'] . "\n";

    if (!empty($result['errors'])) {
        echo implode("\n", $result['errors']) . "\n";
        exit(1);
    }

    exit(0);
}

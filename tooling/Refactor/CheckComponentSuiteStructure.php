<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

final class CheckComponentSuiteStructure
{
    private array $allowedSuites
        = [
            'Application',
            'HTTP',
            'CLI',
            'DataStack',
            'Identity',
            'Operations',
            'Presentation',
            'DeveloperTools',
            'Security',
        ];

    private array $allowedV2Labs
        = [
            'API',          // V2: API Contract Engine - moved to labs/ (2026-05-03)
            'Integration', // V2: Integration Engine - moved to labs/ (2026-05-03)
        ];

    private array $allowedBenchmarks
        = [
            'Performance',  // Stage 14 - moved to benchmarks/ (2026-05-03)
        ];

    private array $allowedTooling
        = [
            'DependencyMap', // moved to tooling/ (2026-05-03)
        ];

    private array $allowedDocs
        = [
            'Components',    // moved to docs/ (2026-05-03)
        ];

    private array $allowedFramework
        = [
            'Adapters',      // Server moved to framework/System/Runtime/Adapters (2026-05-03)
        ];

    private array $allowedBridges
        = [
            'DataFoundation',
            'DataLayer',
        ];

    private array $allowedExperimental
        = [];

    private array $helperFolders
        = [
            'Code-Review-And-ToDo',
            'Context',
            'Dispatcher',
            'Enums',
            'System',
            'URI',
        ];

    private array $errors = [];

    public function check(): array
    {
        $this->checkComponentsRootContainsOnlySuites();
        $this->checkEachSuiteHasSystemRoot();

        return [
            'status' => $this->errors === [] ? 'PASS' : 'FAIL',
            'errors' => $this->errors,
        ];
    }

    private function checkComponentsRootContainsOnlySuites(): void
    {
        $componentsPath = dirname(__DIR__, 2) . '/components';

        if (!is_dir($componentsPath)) {
            $this->errors[] = 'components/ directory not found';

            return;
        }

        $items = scandir($componentsPath);
        foreach ($items as $item) {
            if ($item === '.') {
                continue;
            }

            if ($item === '..') {
                continue;
            }

            if ($item === '.idea') {
                continue;
            }

            $path = $componentsPath . '/' . $item;
            if (!is_dir($path)) {
                continue;
            }

            // Allow known bridges and helper folders
            if (in_array($item, $this->allowedSuites, true)) {
                continue;
            }

            if (in_array($item, $this->allowedBridges, true)) {
                continue;
            }

            if (in_array($item, $this->helperFolders, true)) {
                continue;
            }

            if (in_array($item, $this->allowedExperimental, true)) {
                continue;
            }

            if (in_array($item, $this->allowedV2Labs, true)) {
                $this->errors[] = 'Non-canonical: ' . $item . ' should be in labs/ (V2 locked)';

                continue;
            }

            if (in_array($item, $this->allowedBenchmarks, true)) {
                $this->errors[] = 'Non-canonical: ' . $item . ' should be in benchmarks/';

                continue;
            }

            if (in_array($item, $this->allowedTooling, true)) {
                $this->errors[] = 'Non-canonical: ' . $item . ' should be in tooling/';

                continue;
            }

            if (in_array($item, $this->allowedDocs, true)) {
                $this->errors[] = 'Non-canonical: ' . $item . ' should be in docs/';

                continue;
            }

            if (in_array($item, $this->allowedFramework, true)) {
                $this->errors[] = 'Non-canonical: ' . $item . ' should be in framework/';

                continue;
            }

            $this->errors[] = 'Forbidden item at components/' . $item;
        }

        foreach ($this->allowedSuites as $allowedSuite) {
            if (!is_dir($componentsPath . '/' . $allowedSuite)) {
                $this->errors[] = 'Missing required suite: components/' . $allowedSuite;
            }
        }
    }

    private function checkEachSuiteHasSystemRoot(): void
    {
        $componentsPath = dirname(__DIR__, 2) . '/components';

        // Only check actual component directories, not all folders
        $componentDirs = [
            'Application' => ['Cache', 'Config', 'Container', 'DateTime', 'Filesystem', 'Text', 'Validation'],
            'HTTP' => ['Request', 'Response', 'Router', 'Middleware', 'Session'],  // Security, URI, etc are separate
            'CLI' => ['Console'],
            'DataStack' => ['Data', 'Database', 'Persistence'],
            'Identity' => ['Auth', 'Access', 'Security', 'Tokens'],
            'Operations' => ['Events', 'Logging', 'Mail', 'Queue', 'Notifications', 'ApplicationWorkflow'],
            'Presentation' => ['View'],
            'DeveloperTools' => ['Diagnostics', 'DumpDebugger'],
        ];

        foreach ($componentDirs as $suite => $components) {
            $suitePath = $componentsPath . '/' . $suite;
            if (!is_dir($suitePath)) {
                continue;
            }

            foreach ($components as $component) {
                $componentPath = $suitePath . '/' . $component;
                if (!is_dir($componentPath)) {
                    continue;
                }

                $systemPath = $componentPath . '/System';
                if (!is_dir($systemPath)) {
                    $this->errors[] = sprintf('components/%s/%s missing System/ root', $suite, $component);
                }
            }
        }
    }
}

if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    $checker = new CheckComponentSuiteStructure();
    $result = $checker->check();

    echo $result['status'] . "\n";

    if (!empty($result['errors'])) {
        echo implode("\n", $result['errors']) . "\n";
        exit(1);
    }

    exit(0);
}

<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

final class CheckComponentSuiteStructure
{
    private array $allowedSuites = [
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

    private array $allowedBridges = [
        'DataFoundation',
        'DataLayer',
    ];

    private array $allowedExperimental
        = [
            'Documentation',
            'DependencyMap',
            'Performance',
            'Server',
        ];

    private array $helperFolders = [
        'Code-Review-And-ToDo',
        'Context',
        'Dispatcher',
        'Enums',
        'System',
        'URI',
    ];

    private array $errors = [];

    public function check() : array
    {
        $this->checkComponentsRootContainsOnlySuites();
        $this->checkEachSuiteHasSystemRoot();

        return [
            'status' => empty($this->errors) ? 'PASS' : 'FAIL',
            'errors' => $this->errors,
        ];
    }

    private function checkComponentsRootContainsOnlySuites() : void
    {
        $componentsPath = dirname(__DIR__, 2) . '/components';

        if (! is_dir($componentsPath)) {
            $this->errors[] = 'components/ directory not found';

            return;
        }

        $items = scandir($componentsPath);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..' || $item === '.idea') {
                continue;
            }

            $path = $componentsPath . '/' . $item;
            if (! is_dir($path)) {
                continue;
            }

            // Allow known bridges and helper folders
            if (in_array($item, $this->allowedSuites, true) || in_array($item, $this->allowedBridges, true) || in_array($item, $this->helperFolders, true) || in_array($item, $this->allowedExperimental, true)) {
                continue;
            }

            $this->errors[] = "Forbidden item at components/{$item}";
        }

        foreach ($this->allowedSuites as $suite) {
            if (! is_dir($componentsPath . '/' . $suite)) {
                $this->errors[] = "Missing required suite: components/{$suite}";
            }
        }
    }

    private function checkEachSuiteHasSystemRoot() : void
    {
        $componentsPath = dirname(__DIR__, 2) . '/components';

        // Only check actual component directories, not all folders
        $componentDirs = [
            'Application' => ['Cache', 'Config', 'Container', 'DateTime', 'Filesystem', 'Text', 'Validation'],
            'HTTP'        => ['Request', 'Response', 'Router', 'Middleware', 'Session'],  // Security, URI, etc are separate
            'CLI'         => ['Console'],
            'DataStack'   => ['Data', 'Database', 'Persistence'],
            'Identity'    => ['Auth', 'Access', 'Security', 'Tokens'],
            'Operations'  => ['Events', 'Logging', 'Mail', 'Queue', 'Notifications', 'ApplicationWorkflow'],
            'Presentation' => ['View'],
            'DeveloperTools' => ['Diagnostics', 'DumpDebugger'],
        ];

        foreach ($componentDirs as $suite => $components) {
            $suitePath = $componentsPath . '/' . $suite;
            if (! is_dir($suitePath)) {
                continue;
            }

            foreach ($components as $component) {
                $componentPath = $suitePath . '/' . $component;
                if (! is_dir($componentPath)) {
                    continue;
                }

                $systemPath = $componentPath . '/System';
                if (! is_dir($systemPath)) {
                    $this->errors[] = "components/{$suite}/{$component} missing System/ root";
                }
            }
        }
    }
}

if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    $checker = new CheckComponentSuiteStructure;
    $result = $checker->check();

    echo $result['status'] . "\n";

    if (! empty($result['errors'])) {
        echo implode("\n", $result['errors']) . "\n";
        exit(1);
    }

    exit(0);
}

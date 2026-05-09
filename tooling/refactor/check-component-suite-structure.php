<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

final class CheckComponentSuiteStructure
{
    /** @var list<string> */
    private array $allowedSuites = [
        'Application',
        'API',
        'HTTP',
        'CLI',
        'DataStack',
        'Identity',
        'Operations',
        'Presentation',
        'DeveloperTools',
        'Security',
        'Integration',
        'SystemDesign',
        'Foundation',
    ];

    /** @var list<string> */
    private array $allowedBridges = [
        'DataFoundation',
        'DataLayer',
    ];

    /** @var list<string> */
    private array $allowedExperimental
        = [
            'Documentation',
            'DependencyMap',
            'Performance',
            'Server',
        ];

    /** @var list<string> */
    private array $helperFolders = [
        'EVIDENCE',
        'Context',
        'Dispatcher',
        'Enums',
        'System',
        'URI',
    ];

    /** @var list<string> */
    private array $errors = [];

    /**
     * @return array{status: 'PASS'|'FAIL', errors: list<string>}
     */
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
        $componentsPath = dirname(__DIR__, 2).'/components';

        if (! is_dir($componentsPath)) {
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

            $path = $componentsPath.'/'.$item;
            if (! is_dir($path)) {
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

            $this->errors[] = 'Forbidden item at components/'.$item;
        }

        foreach ($this->allowedSuites as $allowedSuite) {
            if (! is_dir($componentsPath.'/'.$allowedSuite)) {
                $this->errors[] = 'Missing required suite: components/'.$allowedSuite;
            }
        }
    }

    private function checkEachSuiteHasSystemRoot(): void
    {
        $componentsPath = dirname(__DIR__, 2).'/components';

        // Only check actual component directories, not all folders
        /** @var array<string, list<string>> $componentDirs */
        $componentDirs = [
            'Application' => ['Cache', 'Config', 'Container', 'DateTime', 'Filesystem', 'Text', 'Validation'],
            'API' => ['Surface', 'OpenAPI', 'GraphQL'],
            'HTTP' => ['Request', 'Response', 'Router', 'Middleware', 'Session'],  // Security, URI, etc are separate
            'CLI' => ['Console'],
            'DataStack' => ['Data', 'Database', 'Persistence'],
            'Identity' => ['Auth', 'Access', 'Security', 'Tokens'],
            'Operations' => ['Events', 'Logging', 'Mail', 'Queue', 'Notifications', 'ApplicationWorkflow'],
            'Presentation' => ['View'],
            'DeveloperTools' => ['Diagnostics', 'DumpDebugger'],
            'SystemDesign' => [],
        ];

        foreach ($componentDirs as $suite => $components) {
            $suitePath = $componentsPath.'/'.$suite;
            if (! is_dir($suitePath)) {
                continue;
            }

            foreach ($components as $component) {
                $componentPath = $suitePath.'/'.$component;
                if (! is_dir($componentPath)) {
                    continue;
                }

                $systemPath = $componentPath.'/System';
                if (! is_dir($systemPath)) {
                    $this->errors[] = sprintf('components/%s/%s missing System/ root', $suite, $component);
                }
            }
        }
    }
}

if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    $checker = new CheckComponentSuiteStructure();
    $result = $checker->check();

    echo $result['status']."\n";

    if (! empty($result['errors'])) {
        echo implode("\n", $result['errors'])."\n";
        exit(1);
    }

    exit(0);
}

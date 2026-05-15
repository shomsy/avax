<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Scans for ACTIVE components that lack a ServiceProvider.
 *
 * Rules:
 * - Every ACTIVE component MUST have a ServiceProvider
 * - ROADMAP/SCAFFOLD/LABS_ONLY/EVIDENCE_ONLY/TEST_ONLY/PURE_FOUNDATION components are exempt
 * - Empty ServiceProvider shells are forbidden
 * - ServiceProvider MUST have register() with actual bindings
 *
 * Component status is determined by component-status-lock.md and how-this-works.md files.
 */
final class CheckServiceProviderCoverage
{
    /**
     * Component statuses that are exempt from ServiceProvider requirement.
     */
    private array $exemptStatuses
        = [
            'ROADMAP',
            'SCAFFOLD',
            'LABS_ONLY',
            'EVIDENCE_ONLY',
            'TEST_ONLY',
            'PURE_FOUNDATION',
            'DEPRECATED',
        ];

    private array $errors  = [];
    private array $summary = [];

    public function check() : array
    {
        $basePath       = dirname(__DIR__, 2);
        $componentsPath = $basePath . '/components';

        if (! is_dir($componentsPath)) {
            return [
                'status'  => 'PASS',
                'errors'  => [],
                'summary' => ['No components directory found'],
            ];
        }

        $this->scanComponents($componentsPath, $basePath);

        return [
            'status'  => $this->errors === [] ? 'PASS' : 'FAIL',
            'errors'  => $this->errors,
            'summary' => $this->summary,
        ];
    }

    private function scanComponents(string $componentsPath, string $basePath) : void
    {
        // Scan each suite/component
        $suites = array_diff(scandir($componentsPath), ['.', '..']);

        foreach ($suites as $suite) {
            $suitePath = $componentsPath . '/' . $suite;
            if (! is_dir($suitePath)) {
                continue;
            }

            $components = array_diff(scandir($suitePath), ['.', '..']);

            foreach ($components as $component) {
                $componentPath = $suitePath . '/' . $component;
                if (! is_dir($componentPath)) {
                    continue;
                }

                $this->checkComponent($componentPath, $suite, $component, $basePath);
            }
        }
    }

    private function checkComponent(string $componentPath, string $suite, string $component, string $basePath) : void
    {
        // Check if component has a System/ folder
        $systemPath = $componentPath . '/System';
        if (! is_dir($systemPath)) {
            return; // Not a real component
        }

        // Check component status
        $status = $this->getComponentStatus($componentPath);
        if (in_array($status, $this->exemptStatuses, true)) {
            $this->summary[] = sprintf(
                'SKIP: %s/%s (status: %s)',
                $suite,
                $component,
                $status,
            );

            return;
        }

        // Look for ServiceProvider
        $configPath      = $systemPath . '/Configuration';
        $serviceProvider = $this->findServiceProvider($configPath);

        if ($serviceProvider === null) {
            // Check if component has any real PHP files (not just scaffolding)
            if ($this->componentHasRealCode($systemPath)) {
                $this->errors[] = sprintf(
                    'MISSING: %s/%s has real code but no ServiceProvider',
                    $suite,
                    $component,
                );
            } else {
                $this->summary[] = sprintf(
                    'SKIP: %s/%s (no real code)',
                    $suite,
                    $component,
                );
            }

            return;
        }

        // Check if ServiceProvider is empty
        $content = file_get_contents($serviceProvider);
        if ($this->isEmptyServiceProvider($content)) {
            $this->errors[] = sprintf(
                'EMPTY: %s/%s ServiceProvider exists but has no bindings',
                $suite,
                $component,
            );

            return;
        }

        $this->summary[] = sprintf(
            'OK: %s/%s has ServiceProvider (%s)',
            $suite,
            $component,
            basename($serviceProvider),
        );
    }

    private function getComponentStatus(string $componentPath) : string
    {
        // Check how-this-works.md for status
        $howThisWorks = $componentPath . '/System/how-this-works.md';
        if (is_file($howThisWorks)) {
            $content = file_get_contents($howThisWorks);
            foreach ($this->exemptStatuses as $status) {
                if (stripos($content, $status) !== false) {
                    return $status;
                }
            }
        }

        // Check component-status-lock.md
        $statusLock = dirname(__DIR__, 2) . '/EVIDENCE/components/component-status-lock.md';
        if (is_file($statusLock)) {
            $content = file_get_contents($statusLock);
            // Look for component name and status
            $componentName = basename($componentPath);
            if (preg_match('/' . preg_quote($componentName, '/') . '\s*\|.*?(' . implode('|', $this->exemptStatuses) . ')/i', $content, $matches)) {
                return strtoupper($matches[1]);
            }
        }

        return 'ACTIVE';
    }

    private function findServiceProvider(string $configPath) : ?string
    {
        if (! is_dir($configPath)) {
            return null;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($configPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY,
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (str_ends_with($file->getFilename(), 'ServiceProvider.php')) {
                return $file->getPathname();
            }
        }

        return null;
    }

    private function componentHasRealCode(string $systemPath) : bool
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($systemPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY,
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            // Exclude empty scaffold files
            $content = file_get_contents($file->getPathname());
            if (strlen($content) > 100) { // More than just a skeleton
                return true;
            }
        }

        return false;
    }

    private function isEmptyServiceProvider(string $content) : bool
    {
        // Check if register() method has any real code
        if (! preg_match('/function\s+register\s*\(/', $content)) {
            return true;
        }

        // Count actual lines of code in register() (excluding comments, braces, whitespace)
        $lines      = explode("\n", $content);
        $inRegister = false;
        $codeLines  = 0;

        foreach ($lines as $line) {
            if (preg_match('/function\s+register\s*\(/', $line)) {
                $inRegister = true;
                continue;
            }

            if ($inRegister) {
                $trimmed = trim($line);
                if ($trimmed === '' || str_starts_with($trimmed, '//') || $trimmed === '{' || $trimmed === '}') {
                    continue;
                }

                $codeLines++;

                // If we hit another method, stop
                if (preg_match('/function\s+\w+\s*\(/', $line)) {
                    break;
                }
            }
        }

        return $codeLines === 0;
    }
}

if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    $checker = new CheckServiceProviderCoverage();
    $result  = $checker->check();

    echo $result['status'] . "\n";

    if (! empty($result['summary'])) {
        echo "\nSummary:\n";
        echo implode("\n", $result['summary']) . "\n";
    }

    if (! empty($result['errors'])) {
        echo "\nErrors:\n";
        echo implode("\n", $result['errors']) . "\n";
        exit(1);
    }

    exit(0);
}

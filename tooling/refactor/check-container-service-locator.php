<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Scans runtime folders for service locator patterns.
 *
 * Forbidden patterns:
 * - Container::get()
 * - $container->get()
 * - $container->make()
 * - $container->resolve()
 * - app()
 * - resolve()
 *
 * Allowed contexts:
 * - ServiceProvider register()/boot()
 * - System/Configuration/
 * - System/Configuration/Builders/
 * - tests/
 * - tooling/
 */
final class CheckContainerServiceLocator
{
    private array $serviceLocatorPatterns
        = [
            '/\bContainer::get\s*\(/'     => 'Container::get() static call',
            '/\$container->get\s*\(/'     => '$container->get() call',
            '/\$container->make\s*\(/'    => '$container->make() call',
            '/\$container->resolve\s*\(/' => '$container->resolve() call',
            '/\bapp\s*\(\s*[\'"]/'        => 'app() helper with class argument',
            '/\bresolve\s*\(\s*[\'"]/'    => 'resolve() helper with class argument',
        ];

    private array $scanRoots
        = [
            'framework/System/Flows',
            'framework/System/Capabilities',
            'framework/System/PublicSurface',
        ];

    private array $allowedContexts
        = [
            '/Configuration/',
            '/Configuration/Builders/',
            '/Builders/',
            'ServiceProvider',
            '/tests/',
            '/Tests/',
            '/tooling/',
            '/Tooling/',
            '/Application/Container/',
        ];

    private array $compositionRoots
        = [
            'framework/System/PublicSurface/Avax.php',
            'framework/System/Flows/CreateApplication/CreateApplication.php',
            'framework/System/Flows/BootApplication/BuildApplicationState.php',
            'framework/System/Flows/RunApplication/RunApplication.php',
        ];

    private array $errors = [];

    public function check() : array
    {
        $basePath = dirname(__DIR__, 2);

        foreach ($this->scanRoots as $root) {
            $fullPath = $basePath . '/' . $root;
            $this->scanDirectory($fullPath, $basePath);
        }

        $componentsPath = $basePath . '/components';
        if (is_dir($componentsPath)) {
            $this->scanComponentDirectories($componentsPath, $basePath);
        }

        return [
            'status' => $this->errors === [] ? 'PASS' : 'FAIL',
            'errors' => $this->errors,
        ];
    }

    private function scanDirectory(string $directory, string $basePath) : void
    {
        if (! is_dir($directory)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY,
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace($basePath . '/', '', $file->getPathname());

            if ($this->isInAllowedContext($relativePath)) {
                continue;
            }

            $this->scanFile($file, $relativePath);
        }
    }

    private function isInAllowedContext(string $relativePath) : bool
    {
        foreach ($this->allowedContexts as $context) {
            if (str_contains($relativePath, $context)) {
                return true;
            }
        }

        return false;
    }

    private function scanFile(SplFileInfo $file, string $relativePath) : void
    {
        if (in_array($relativePath, $this->compositionRoots, true)) {
            return;
        }

        $content = file_get_contents($file->getPathname());
        $lines   = explode("\n", $content);

        foreach ($lines as $lineNumber => $line) {
            $trimmed = ltrim($line);
            if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '*') || str_starts_with($trimmed, '/*')) {
                continue;
            }

            foreach ($this->serviceLocatorPatterns as $pattern => $description) {
                if (preg_match($pattern, $line)) {
                    $this->errors[] = sprintf(
                        '%s:%d — %s',
                        $relativePath,
                        $lineNumber + 1,
                        $description,
                    );
                }
            }
        }
    }

    private function scanComponentDirectories(string $componentsPath, string $basePath) : void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($componentsPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY,
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace($basePath . '/', '', $file->getPathname());

            if (! $this->isInComponentSystemPath($relativePath)) {
                continue;
            }

            if ($this->isInAllowedContext($relativePath)) {
                continue;
            }

            $this->scanFile($file, $relativePath);
        }
    }

    private function isInComponentSystemPath(string $relativePath) : bool
    {
        return str_contains($relativePath, '/System/Flows/')
            || str_contains($relativePath, '/System/Capabilities/')
            || str_contains($relativePath, '/System/PublicSurface/');
    }
}

if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    $checker = new CheckContainerServiceLocator();
    $result  = $checker->check();

    echo $result['status'] . "\n";

    if (! empty($result['errors'])) {
        echo implode("\n", $result['errors']) . "\n";
        exit(1);
    }

    exit(0);
}

<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class CheckRuntimeCompositionLeaks
{
    /**
     * Patterns that indicate runtime composition leaks.
     * Keys are regex patterns, values are human-readable descriptions.
     */
    private array $leakPatterns = [
        '/class_exists\s*\(/'                          => 'Runtime class discovery (class_exists)',
        '/new\s+Build[A-Z]/'                           => 'Builder instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Middleware\b/'           => 'Middleware instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Handler\b/'              => 'Handler instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Dispatcher\b/'           => 'Dispatcher instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Resolver\b/'             => 'Resolver instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Factory\b/'              => 'Factory instantiation in runtime code',
        '/->build\s*\(\)/'                              => 'Builder build() call in runtime code',
        '/\$middleware\s*\[\]\s*=/'                     => 'Middleware stack construction at runtime',
        '/\$pipeline\s*\[\]\s*=/'                       => 'Pipeline construction at runtime',
        '/\?\?\s*new\s+/'                              => 'Null-coalescing fallback instantiation',
    ];

    /**
     * Base directories to scan for runtime composition leaks.
     */
    private array $scanRoots = [
        'framework/System/Flows',
        'framework/System/Capabilities',
        'framework/System/PublicSurface',
    ];

    /**
     * Contexts to exclude from scanning (allowed to use new/Build).
     */
    private array $allowedContexts = [
        '/Configuration/',
        '/Configuration/Builders/',
        '/Builders/',
        'ServiceProvider',
        '/Foundation/',
        '/tests/',
        '/Tests/',
        '/tooling/',
        '/Tooling/',
        '/Application/Container/',
    ];

    /**
     * Specific files that are composition roots (allowed to assemble).
     */
    private array $compositionRoots = [
        'framework/System/PublicSurface/Avax.php',
        'framework/System/Flows/CreateApplication/CreateApplication.php',
        'framework/System/Flows/BootApplication/BuildApplicationState.php',
        'framework/System/Flows/RunApplication/RunApplication.php',
    ];

    /**
     * Files to exclude by name (data objects, not services).
     */
    private array $allowedFileSuffixes = [
        'Event.php',
        'Exception.php',
        'Enum.php',
    ];

    private array $errors = [];

    public function check(): array
    {
        $basePath = dirname(__DIR__, 2);

        foreach ($this->scanRoots as $root) {
            $fullPath = $basePath.'/'.$root;
            $this->scanDirectory($fullPath, $basePath);
        }

        // Also scan component System/ directories
        $componentsPath = $basePath.'/components';
        if (is_dir($componentsPath)) {
            $this->scanComponentDirectories($componentsPath, $basePath);
        }

        return [
            'status' => $this->errors === [] ? 'PASS' : 'FAIL',
            'errors' => $this->errors,
        ];
    }

    private function scanDirectory(string $directory, string $basePath): void
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

            $relativePath = str_replace($basePath.'/', '', $file->getPathname());

            if ($this->isInAllowedContext($relativePath)) {
                continue;
            }

            $this->scanFile($file, $relativePath);
        }
    }

    private function scanComponentDirectories(string $componentsPath, string $basePath): void
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

            $relativePath = str_replace($basePath.'/', '', $file->getPathname());

            // Only scan System/ subdirectories in components
            if (! $this->isInComponentSystemPath($relativePath)) {
                continue;
            }

            if ($this->isInAllowedContext($relativePath)) {
                continue;
            }

            $this->scanFile($file, $relativePath);
        }
    }

    private function isInComponentSystemPath(string $relativePath): bool
    {
        return str_contains($relativePath, '/System/Flows/')
            || str_contains($relativePath, '/System/Capabilities/')
            || str_contains($relativePath, '/System/PublicSurface/');
    }

    private function isInAllowedContext(string $relativePath): bool
    {
        foreach ($this->allowedContexts as $context) {
            if (str_contains($relativePath, $context)) {
                return true;
            }
        }

        foreach ($this->allowedFileSuffixes as $suffix) {
            if (str_ends_with($relativePath, $suffix)) {
                return true;
            }
        }

        return false;
    }

    private function scanFile(SplFileInfo $file, string $relativePath): void
    {
        // Skip composition roots
        if (in_array($relativePath, $this->compositionRoots, true)) {
            return;
        }

        $content = file_get_contents($file->getPathname());
        $lines = explode("\n", $content);

        foreach ($lines as $lineNumber => $line) {
            // Skip comments
            $trimmed = ltrim($line);
            if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '*') || str_starts_with($trimmed, '/*')) {
                continue;
            }

            foreach ($this->leakPatterns as $pattern => $description) {
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
}

if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    $checker = new CheckRuntimeCompositionLeaks();
    $result = $checker->check();

    echo $result['status']."\n";

    if (! empty($result['errors'])) {
        echo implode("\n", $result['errors'])."\n";
        exit(1);
    }

    exit(0);
}

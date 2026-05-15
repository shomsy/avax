<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * CheckRuntimeCompositionLeaks — Gate that detects composition leaks in runtime execution paths.
 *
 * Rule: "Value objects can be new. Services must be injected. Composition roots may assemble.
 *        Runtime execution must only execute."
 *
 * Severity levels:
 *   HIGH   — Service instantiation, lazy composition, class discovery in runtime paths
 *   MEDIUM — Builder patterns, pipeline assembly that should be boot-time
 *   INFO   — Patterns that are suspicious but may be legitimate value objects
 */
final class CheckRuntimeCompositionLeaks
{
    // ─── HIGH severity: service-level leaks ────────────────────────────────

    /** @var array<string, string> */
    private array $highSeverityPatterns = [
        '/\bclass_exists\s*\(/'                                          => 'Runtime class discovery (class_exists)',
        '/\binterface_exists\s*\(/'                                      => 'Runtime interface discovery (interface_exists)',
        '/new\s+Build[A-Z][a-zA-Z]*\b/'                                  => 'Builder instantiation in runtime code',
        '/->build\s*\(\)/'                                               => 'Builder build() call in runtime code',
        '/\?\?\s*new\s+[A-Z]/'                                           => 'Null-coalescing fallback to new service',
        '/\?\?=\s*new\s+[A-Z]/'                                          => 'Lazy singleton-style composition',
    ];

    // ─── MEDIUM severity: suspicious but sometimes legitimate ──────────────

    /** @var array<string, string> */
    private array $mediumSeverityPatterns = [
        '/new\s+[A-Z][a-zA-Z]*Middleware\b/'                             => 'Middleware instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Handler\b(?!er\b)/'                        => 'Handler instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Dispatcher\b/'                             => 'Dispatcher instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Resolver\b/'                               => 'Resolver instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Factory\b/'                                => 'Factory instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Service\b/'                                => 'Service instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Provider\b/'                               => 'Provider instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Engine\b/'                                 => 'Engine instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Manager\b/'                                => 'Manager instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Registry\b/'                               => 'Registry instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Repository\b/'                             => 'Repository instantiation in runtime code',
        '/new\s+[A-Z][a-zA-Z]*Collector\b/'                              => 'Collector instantiation in runtime code',
    ];

    /** @var list<string> */
    private array $scanRoots = [
        'framework/System/Flows',
        'framework/System/Capabilities',
        'framework/System/PublicSurface',
    ];

    /**
     * Contexts where new/build is always allowed (composition roots, config, tests).
     */
    private array $allowedContexts = [
        '/Configuration/',
        '/Configuration/Builders/',
        'ServiceProvider',
        '/tests/',
        '/Tests/',
        '/tooling/',
        '/Tooling/',
    ];

    /**
     * Explicit composition roots — allowed to assemble any graph.
     */
    private array $compositionRoots = [
        'framework/System/PublicSurface/Avax.php',
        'framework/System/Flows/CreateApplication/CreateApplication.php',
        'framework/System/Flows/BootApplication/BuildApplicationState.php',
        'framework/System/Flows/RunApplication/RunApplication.php',
    ];

    /**
     * File types that are value objects — always allowed to use `new`.
     */
    private array $allowedFileSuffixes = [
        'Event.php',
        'Exception.php',
        'Enum.php',
        'Result.php',
        'ValueObject.php',
        'DTO.php',
    ];

    /**
     * Known allowances: specific files where a pattern is explicitly allowed.
     * Format: relative_path => [line_number => reason]
     */
    private array $knownAllowances = [
        // App.php: OpenHttpRequestScope/CloseHttpRequestScope receive data from RuntimeInterface (injected)
        'framework/System/PublicSurface/App.php' => [
            'new OpenHttpRequestScope' => 'Thin wrapper around injected runtime state; data from RuntimeInterface',
            'new CloseHttpRequestScope' => 'Thin wrapper around injected runtime state; data from RuntimeInterface',
        ],
        // MatchHttpRoute: RouteCollection is a value object (no constructor deps)
        'framework/System/Flows/HandleIncomingHttp/MatchHttpRoute.php' => [
            'new RouteCollection' => 'Value object with no constructor dependencies; assembled from already-resolved data',
        ],
        // HandleIncomingHttp: scope objects receive data from RuntimeInterface
        'framework/System/Flows/HandleIncomingHttp/HandleIncomingHttp.php' => [
            'new OpenHttpRequestScope' => 'Thin wrapper; receives RequestScopeStore from RuntimeInterface',
            'new CloseHttpRequestScope' => 'Thin wrapper; receives RequestScopeStore from RuntimeInterface',
        ],
        // Concurrency facade: StartTask/WaitForTask are stateless flows
        'components/Operations/Concurrency/System/PublicSurface/Concurrency.php' => [
            'new StartTask' => 'Stateless Flow with no dependencies; VALUE_OBJECT_ALLOWED',
            'new WaitForTask' => 'Stateless Flow with no dependencies; VALUE_OBJECT_ALLOWED',
        ],
        // Avax.php: composition root assembling boot-time objects
        'framework/System/PublicSurface/Avax.php' => [
            'new CreateRequestFromGlobals' => 'Composition root (Avax::create); boot-time assembly',
            'new HandleIncomingHttp' => 'Composition root (Avax::create); boot-time assembly',
        ],
    ];

    /** @var list<array{severity: string, file: string, line: int, pattern: string, description: string}> */
    private array $errors = [];

    public function check(): array
    {
        $basePath = dirname(__DIR__, 2);

        foreach ($this->scanRoots as $root) {
            $fullPath = $basePath . '/' . $root;
            $this->scanDirectory($fullPath, $basePath);
        }

        // Also scan component System/ directories
        $componentsPath = $basePath . '/components';
        if (is_dir($componentsPath)) {
            $this->scanComponentDirectories($componentsPath, $basePath);
        }

        return [
            'status' => $this->errors === [] ? 'PASS' : 'FAIL',
            'errors' => $this->formatErrors(),
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

            $relativePath = str_replace($basePath . '/', '', $file->getPathname());

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
        // Skip explicit composition roots
        if (in_array($relativePath, $this->compositionRoots, true)) {
            return;
        }

        $content = file_get_contents($file->getPathname());
        if ($content === false) {
            return;
        }
        $lines = explode("\n", $content);

        foreach ($lines as $lineNumber => $line) {
            // Skip comments
            $trimmed = ltrim($line);
            if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '*') || str_starts_with($trimmed, '/*')) {
                continue;
            }

            $lineText = trim($line);

            // Check HIGH severity patterns
            foreach ($this->highSeverityPatterns as $pattern => $description) {
                if (preg_match($pattern, $line)) {
                    $matchedText = $this->extractMatchedText($line, $pattern);
                    if (! $this->isKnownAllowed($relativePath, $matchedText)) {
                        $this->recordError('HIGH', $relativePath, $lineNumber + 1, $description, $lineText);
                    }
                }
            }

            // Check MEDIUM severity patterns
            foreach ($this->mediumSeverityPatterns as $pattern => $description) {
                if (preg_match($pattern, $line)) {
                    $matchedText = $this->extractMatchedText($line, $pattern);
                    if (! $this->isKnownAllowed($relativePath, $matchedText)) {
                        $this->recordError('MEDIUM', $relativePath, $lineNumber + 1, $description, $lineText);
                    }
                }
            }
        }
    }

    private function extractMatchedText(string $line, string $pattern): string
    {
        if (preg_match($pattern, $line, $matches)) {
            return trim($matches[0]);
        }
        return trim($line);
    }

    private function isKnownAllowed(string $relativePath, string $matchedText): bool
    {
        if (! isset($this->knownAllowances[$relativePath])) {
            return false;
        }

        foreach ($this->knownAllowances[$relativePath] as $allowedPattern => $reason) {
            if (str_contains($matchedText, $allowedPattern)) {
                return true;
            }
        }

        return false;
    }

    private function recordError(string $severity, string $file, int $line, string $description, string $lineText): void
    {
        $this->errors[] = [
            'severity' => $severity,
            'file' => $file,
            'line' => $line,
            'description' => $description,
            'lineText' => $lineText,
        ];
    }

    /** @return list<string> */
    private function formatErrors(): array
    {
        $formatted = [];
        foreach ($this->errors as $error) {
            $formatted[] = sprintf(
                '[%s] %s:%d — %s',
                $error['severity'],
                $error['file'],
                $error['line'],
                $error['description'],
            );
        }
        return $formatted;
    }
}

if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    $checker = new CheckRuntimeCompositionLeaks();
    $result = $checker->check();

    echo $result['status'] . "\n";

    if (! empty($result['errors'])) {
        echo implode("\n", $result['errors']) . "\n";
        exit(1);
    }

    exit(0);
}

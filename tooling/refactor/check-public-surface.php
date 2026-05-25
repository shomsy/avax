<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * CheckPublicSurface — Gate that validates PublicSurface integrity.
 *
 * PublicSurface must be a thin delegation boundary, not a place for machinery.
 *
 * Checks:
 *   - Excessive private state (>3 private properties with assignment)
 *   - Business logic in methods (if/throw/switch/for/while/try beyond delegation)
 *   - Hidden construction (new statements for non-value-objects)
 *   - Service locator usage (static singleton patterns)
 *   - Static mutable state (private static $prop without reset lifecycle)
 *   - Method complexity (methods over 15 lines)
 *   - Total line count (files over 150 lines)
 */
final class CheckPublicSurface
{
    /**
     * Baseline data loaded from tooling/governance/baselines/public-surface.php.
     * Structure: ['AreaName' => ['check_type' => [...]]]
     *
     * @var array<string, array<string, array<string, mixed>>>|null
     */
    private ?array $baseline = null;

    /**
     * File suffixes that are value objects — always allowed to use `new`.
     */
    private array $valueObjectSuffixes = [
        'Event.php',
        'Exception.php',
        'Enum.php',
        'Result.php',
        'ValueObject.php',
        'DTO.php',
        'Data.php',
        'Record.php',
        'Context.php',
    ];

    /** @var list<array{check: string, severity: string, file: string, line: int, description: string, method: string}> */
    private array $errors = [];

    /**
     * Load the baseline allowance file if it exists.
     */
    public function loadBaseline(?string $path = null): void
    {
        $path ??= dirname(__DIR__).'/governance/baselines/public-surface.php';
        if (is_file($path)) {
            $this->baseline = require $path;
        }
    }

    /**
     * Check whether a finding matches a baseline entry.
     *
     * A finding matches when:
     * 1. The file's component area matches a baseline key
     * 2. The check type matches a baseline sub-key
     * 3. The file basename matches a listed file or the '*' wildcard
     */
    private function matchesBaseline(array $error): bool
    {
        if ($this->baseline === null) {
            return false;
        }

        $file = $error['file'];
        $check = $error['check'];

        // Extract component area from path: components/Area/...
        if (! preg_match('#components/([^/]+)/#', $file, $areaMatch)) {
            return false;
        }
        $area = $areaMatch[1];

        if (! isset($this->baseline[$area])) {
            return false;
        }
        if (! isset($this->baseline[$area][$check])) {
            return false;
        }

        $files = $this->baseline[$area][$check]['files'] ?? [];
        $basename = basename($file);

        // Check '*' wildcard or exact file match
        return isset($files['*']) || isset($files[$basename]);
    }

    /**
     * Classify findings against the baseline.
     *
     * - Findings matching the baseline become YELLOW (accepted debt).
     * - Findings NOT matching the baseline keep their original severity.
     *
     * Returns a result array with:
     *   - status: PASS (no unclassified findings), FAIL (unclassified BLOCKER/HIGH)
     *   - errors: raw error array (unchanged)
     *   - yellow: list of baseline-matched findings
     *   - unclassified: list of findings not in baseline
     */
    public function check(): array
    {
        $this->errors = [];
        $this->checkPublicSurfaceClassesAreThin();
        $this->checkBusinessLogicInPublicSurface();
        $this->checkHiddenConstructionInPublicSurface();
        $this->checkServiceLocatorUsageInPublicSurface();
        $this->checkStaticMutableStateInPublicSurface();
        $this->checkMethodComplexityInPublicSurface();
        $this->checkPublicSurfaceLineCount();

        $yellow = [];
        $unclassified = [];

        foreach ($this->errors as $error) {
            if ($this->matchesBaseline($error)) {
                $yellow[] = $error;
            } else {
                $unclassified[] = $error;
            }
        }

        // FAIL only if there are unclassified BLOCKER or HIGH findings
        $hasUnclassifiedHigh = array_filter(
            $unclassified,
            static fn ($e) => in_array($e['severity'], ['BLOCKER', 'HIGH'], true),
        );

        $status = $hasUnclassifiedHigh !== [] ? 'FAIL' : 'PASS';

        return [
            'status' => $status,
            'errors' => $this->errors,
            'yellow' => $yellow,
            'unclassified' => $unclassified,
        ];
    }

    // ─── Check A: Excessive private state (original) ─────────────────────

    private function checkPublicSurfaceClassesAreThin(): void
    {
        $componentsPath = dirname(__DIR__, 2).'/components';
        $this->scanPublicSurfaceFiles($componentsPath, function (string $path, string $content) : void {
            $privateCount = preg_match_all('/private\s+\w+\s+\$\w+\s*=/', $content);
            if ($privateCount > 3) {
                $this->recordError(
                    'private_state',
                    'HIGH',
                    $path,
                    0,
                    "PublicSurface has excessive private state ({$privateCount} properties)",
                );
            }
        });
    }

    // ─── Check B: Business logic detection ───────────────────────────────

    private function checkBusinessLogicInPublicSurface(): void
    {
        $componentsPath = dirname(__DIR__, 2).'/components';
        $this->scanPublicSurfaceFiles($componentsPath, function (string $path, string $content) : void {
            $methods = $this->extractMethods($content);
            foreach ($methods as $method) {
                if ($this->isPureDelegation($method['body'])) {
                    continue;
                }

                if ($this->hasBusinessLogic($method['body'], $method['name'])) {
                    $this->recordError(
                        'business_logic',
                        'HIGH',
                        $path,
                        $method['line'],
                        "Method {$method['name']} contains business logic beyond simple delegation",
                        $method['name'],
                    );
                }
            }
        });
    }

    // ─── Check C: Hidden construction ────────────────────────────────────

    private function checkHiddenConstructionInPublicSurface(): void
    {
        $componentsPath = dirname(__DIR__, 2).'/components';
        $this->scanPublicSurfaceFiles($componentsPath, function (string $path, string $content) : void {
            if ($this->isValueObjectFile($path)) {
                return;
            }

            $lines = explode("\n", $content);
            foreach ($lines as $lineNum => $line) {
                $trimmed = ltrim($line);
                if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '*') || str_starts_with($trimmed, '/*')) {
                    continue;
                }

                // Match `new ClassName` but exclude value objects and interfaces
                if (preg_match('/\bnew\s+([A-Z][A-Za-z0-9_\\\\]+)\b/', $line, $matches)) {
                    $className = $matches[1];
                    if ($this->isValueObjectClass($className)) {
                        continue;
                    }
                    // Skip new self() and new static()
                    if (in_array($className, ['self', 'static'], true)) {
                        continue;
                    }

                    $this->recordError(
                        'hidden_construction',
                        'HIGH',
                        $path,
                        $lineNum + 1,
                        "PublicSurface constructs {$className} — should delegate to injected dependency",
                    );
                }
            }
        });
    }

    // ─── Check D: Service locator usage ──────────────────────────────────

    private function checkServiceLocatorUsageInPublicSurface(): void
    {
        $componentsPath = dirname(__DIR__, 2).'/components';
        $this->scanPublicSurfaceFiles($componentsPath, function (string $path, string $content) : void {
            // Detect static singleton patterns: ::instance(), ::getInstance()
            if (preg_match('/::(instance|getInstance|get)\s*\(/', $content)) {
                $hasReset = preg_match('/public\s+static\s+function\s+(reset|resetInstance)\s*\(/', $content);
                $severity = $hasReset ? 'MEDIUM' : 'HIGH';
                $note = $hasReset ? ' (has reset lifecycle — YELLOW)' : '';
                $this->recordError(
                    'service_locator',
                    $severity,
                    $path,
                    0,
                    "PublicSurface uses static singleton pattern{$note}",
                );
            }
        });
    }

    // ─── Check E: Static mutable state ───────────────────────────────────

    private function checkStaticMutableStateInPublicSurface(): void
    {
        $componentsPath = dirname(__DIR__, 2).'/components';
        $this->scanPublicSurfaceFiles($componentsPath, function (string $path, string $content) : void {
            // Detect private/protected static $prop with mutable assignment
            // Handles typed properties: `private static ?Auth $instance = null`
            // and untyped: `private static $instance = null`
            if (preg_match('/(private|protected)\s+static\s+(\?\w+[\[\]]?|\w+(?:\|\w+)*[\[\]]?)?\s*\$\w+\s*=/', $content)) {
                $hasReset = preg_match('/public\s+static\s+function\s+(reset|resetInstance|setInstance)\s*\(/', $content);
                $severity = $hasReset ? 'MEDIUM' : 'HIGH';
                $note = $hasReset ? ' (has lifecycle management — YELLOW)' : '';
                $this->recordError(
                    'static_mutable_state',
                    $severity,
                    $path,
                    0,
                    "PublicSurface has mutable static state{$note}",
                );
            }
        });
    }

    // ─── Check F: Method complexity ──────────────────────────────────────

    private function checkMethodComplexityInPublicSurface(): void
    {
        $componentsPath = dirname(__DIR__, 2).'/components';
        $this->scanPublicSurfaceFiles($componentsPath, function (string $path, string $content) : void {
            $methods = $this->extractMethods($content);
            foreach ($methods as $method) {
                $lineCount = substr_count($method['body'], "\n") + 1;
                if ($lineCount > 15) {
                    $this->recordError(
                        'method_complexity',
                        'MEDIUM',
                        $path,
                        $method['line'],
                        "Method {$method['name']} is {$lineCount} lines (threshold: 15)",
                        $method['name'],
                    );
                }
            }
        });
    }

    // ─── Check G: Total line count ───────────────────────────────────────

    private function checkPublicSurfaceLineCount(): void
    {
        $componentsPath = dirname(__DIR__, 2).'/components';
        $this->scanPublicSurfaceFiles($componentsPath, function (string $path, string $content) : void {
            $lineCount = substr_count($content, "\n") + 1;
            if ($lineCount > 150) {
                $this->recordError(
                    'line_count',
                    'HIGH',
                    $path,
                    0,
                    "PublicSurface file is {$lineCount} lines (threshold: 150)",
                );
            }
        });
    }

    // ─── Scanning helpers ────────────────────────────────────────────────

    /**
     * Scan all PublicSurface PHP files in a path, applying a callback to each.
     */
    private function scanPublicSurfaceFiles(string $path, callable $callback): void
    {
        if (! is_dir($path)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
            RecursiveIteratorIterator::SELF_FIRST,
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            if (! str_contains($file->getPathname(), '/PublicSurface/')) {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            if ($content === false) {
                continue;
            }

            $relativePath = $file->getPathname();
            $callback($relativePath, $content);
        }
    }

    /**
     * Extract methods from PHP class content.
     *
     * @return list<array{name: string, body: string, line: int}>
     */
    private function extractMethods(string $content): array
    {
        $methods = [];
        $lines = explode("\n", $content);
        $inMethod = false;
        $methodName = '';
        $methodBody = '';
        $methodLine = 0;
        $braceDepth = 0;

        foreach ($lines as $i => $line) {
            if (! $inMethod) {
                // Look for method declarations
                if (preg_match('/^\s*(public|protected|private)(\s+static)?\s+function\s+(\w+)\s*\(/', $line, $matches)) {
                    $inMethod = true;
                    $methodName = $matches[3];
                    $methodLine = $i + 1;
                    $methodBody = $line;
                    $braceDepth = 0;

                    // Count braces in the declaration line
                    $braceDepth += substr_count($line, '{');
                    $braceDepth -= substr_count($line, '}');

                    if ($braceDepth <= 0 && str_contains($line, '{')) {
                        // Single-line method or empty body
                        $methods[] = [
                            'name' => $methodName,
                            'body' => $methodBody,
                            'line' => $methodLine,
                        ];
                        $inMethod = false;
                    }
                }
            } else {
                $methodBody .= "\n".$line;
                $braceDepth += substr_count($line, '{');
                $braceDepth -= substr_count($line, '}');

                if ($braceDepth <= 0) {
                    $methods[] = [
                        'name' => $methodName,
                        'body' => $methodBody,
                        'line' => $methodLine,
                    ];
                    $inMethod = false;
                }
            }
        }

        return $methods;
    }

    /**
     * Check if a method body is purely delegating.
     *
     * A pure delegation method has a body that only:
     * - Returns a call on $this->dependency
     * - Calls a method on $this->dependency
     * - Returns null, true, false, or simple expressions
     * - Has simple null-coalescing delegation
     */
    private function isPureDelegation(string $body): bool
    {
        // Strip comments and strings for analysis
        $code = preg_replace('/\/\/.*/', '', $body);
        $code = preg_replace('/\/\*.*?\*\//s', '', $code);

        // Pure delegation patterns
        $delegationPatterns = [
            '/return\s+\$this->\w+->\w+\s*\([^)]*\)\s*;/',                // return $this->dep->method()
            '/\$this->\w+->\w+\s*\([^)]*\)\s*;/',                         // $this->dep->method()
            '/return\s+\$this->\w+->\w+\s*\([^)]*\)\s*(\?\?[^;]+)?\s*;/', // return $this->dep->method() ?? default
            '/return\s*(true|false|null|new\s+\w+)\s*;/',                  // return true/false/null/new
            '/^\s*(public|protected|private)\s+.*function.*\(\)\s*:\s*\w+\s*\{\s*\}/s', // empty method
            '/function\s+\w+\s*\([^)]*\)\s*\{\s*return\s+/',             // simple return
        ];

        foreach ($delegationPatterns as $pattern) {
            // For short methods, check if they match a delegation pattern
            $trimmed = trim($code);
            if (preg_match($pattern, $trimmed)) {
                return true;
            }
        }

        // Short methods (less than 3 lines of actual code) are likely delegation
        $codeLines = array_filter(array_map('trim', explode("\n", $code)), static fn ($l) => $l !== '' && $l !== '{' && $l !== '}');
        if (count($codeLines) <= 3) {
            // Check if it contains delegation keywords
            if (preg_match('/\$this->\w+->/', $code) || preg_match('/return\s/', $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a method body contains business logic beyond delegation.
     */
    private function hasBusinessLogic(string $body, string $methodName): bool
    {
        // Skip constructors and lifecycle methods
        if (in_array($methodName, ['__construct', 'reset', 'resetInstance', 'setInstance'], true)) {
            return false;
        }

        // Strip comments and strings
        $code = preg_replace('/\/\/.*/', '', $body);
        $code = preg_replace('/\/\*.*?\*\//s', '', $code);
        $code = preg_replace('/"[^"]*"/', '""', $code);
        $code = preg_replace("/'[^']*'/", "''", $code);

        // Count control flow constructs
        $controlFlow = 0;
        $controlFlow += preg_match_all('/\b(if|switch|for|while|foreach|try)\b/', $code);

        // More than 2 control flow constructs suggests business logic
        return $controlFlow > 2;
    }

    /**
     * Check if a file is a value object file.
     */
    private function isValueObjectFile(string $path): bool
    {
        foreach ($this->valueObjectSuffixes as $suffix) {
            if (str_ends_with($path, $suffix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a class name looks like a value object.
     */
    private function isValueObjectClass(string $className): bool
    {
        $valueObjectKeywords = [
            'Exception', 'Event', 'Enum', 'Result', 'DTO', 'Data',
            'Record', 'Context', 'Request', 'Response', 'Value',
            'Input', 'Output', 'Payload', 'Message', 'Token',
        ];

        $lastBackslash = strrpos($className, '\\');
        $baseName = $lastBackslash !== false ? substr($className, $lastBackslash + 1) : $className;

        foreach ($valueObjectKeywords as $keyword) {
            if (str_ends_with($baseName, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function recordError(
        string $check,
        string $severity,
        string $file,
        int $line,
        string $description,
        string $method = '',
    ): void {
        $this->errors[] = [
            'check' => $check,
            'severity' => $severity,
            'file' => $file,
            'line' => $line,
            'description' => $description,
            'method' => $method,
        ];
    }

    /** @return list<string> */
    private function formatErrors(): array
    {
        $formatted = [];
        foreach ($this->errors as $error) {
            $formatted[] = sprintf(
                '[%s] (%s) %s:%d — %s',
                $error['severity'],
                $error['check'],
                $error['file'],
                $error['line'],
                $error['description'],
            );
        }

        return $formatted;
    }
}

if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    $checker = new CheckPublicSurface();
    $checker->loadBaseline();
    $result = $checker->check();

    // Print unclassified findings first (these are the real failures)
    if (! empty($result['unclassified'])) {
        foreach ($result['unclassified'] as $error) {
            echo sprintf(
                '[%s] (%s) %s:%d — %s',
                $error['severity'],
                $error['check'],
                $error['file'],
                $error['line'],
                $error['description'],
            )."\n";
        }
    }

    // Print YELLOW findings as accepted debt
    if (! empty($result['yellow'])) {
        $yellowCount = count($result['yellow']);
        echo "\nYELLOW (baseline-accepted debt — {$yellowCount} findings):\n";
        foreach ($result['yellow'] as $error) {
            echo sprintf(
                '  [%s] (%s) %s:%d — %s',
                'YELLOW',
                $error['check'],
                basename($error['file']),
                $error['line'],
                $error['description'],
            )."\n";
        }
        echo "  (See tooling/governance/baselines/public-surface.php for details)\n";
    }

    echo $result['status']."\n";

    if ($result['status'] === 'FAIL') {
        exit(1);
    }

    exit(0);
}

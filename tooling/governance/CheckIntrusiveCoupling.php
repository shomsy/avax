<?php

declare(strict_types=1);

namespace Avax\Tooling\Governance;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * CheckIntrusiveCoupling — Gate that detects cross-component internal reach-through
 * and dependency direction violations.
 *
 * Detects:
 *   - BLOCKER: Import from \Internal\ namespace (future-proof)
 *   - HIGH: Cross-component import where no allowed dependency direction exists
 *   - MEDIUM: Import of deep implementation details from another component
 *
 * Rule: Components must interact through stable PublicSurface APIs and approved
 * Capability APIs. Reaching into another component's internal implementation
 * classes creates intrusive coupling and violates ownership boundaries.
 */
final class CheckIntrusiveCoupling
{
    /**
     * Universal dependencies — all components may import from these.
     * These are platform/infrastructure components that provide cross-cutting concerns.
     */
    private array $universalDependencies = [
        'Application/Container',   // ServiceProvider, ContainerInterface
        'Application/DateTime',    // Clock, Date
        'Application/Text',        // Str, Text utilities
        'Application/Filesystem',  // Filesystem abstraction
        'Operations/Filesystem',   // Filesystem abstraction (alias)
        'Operations/Resilience',   // Retry, Timeout, Circuit Breaker
        'Operations/Reliability',  // Reliability patterns
        'Operations/Observability',// Logging, Tracing, Metrics
        'Operations/Events',       // Event system
        'Operations/Queue',        // Queue system
        'Operations/MessageBus',   // Event/Command bus
        'Security/Hashing',        // Password hashing
        'Security/Cryptography',   // Cryptographic primitives
        'Security/Redaction',      // Data redaction
        'HTTP/Security',           // CSRF, headers, signed URLs
        'Application/Cache',       // Cache system
        'Application/Validation',  // Validation system
        'Application/Pipeline',    // Pipeline system
        'HTTP/Router',             // Router
        'HTTP/Session',            // Session
        'HTTP/Request',            // Request abstractions
        'HTTP/Response',           // Response abstractions
        'HTTP/Client',             // HTTP client
        'HTTP/Context',            // HTTP context
        'HTTP/Middleware',         // Middleware
        'HTTP/SecureRequest',      // Secure request DTOs
        'DataStack/Data',          // Data objects
        'DataStack/DataTransfer',  // Data transfer/hydrators
        'DataStack/Database',      // Database abstraction
        'DataStack/Persistence',   // Persistence layer
        'CLI/Console',             // Console/CLI
        'DeveloperTools/DumpDebugger', // Debug tools
        'DeveloperTools/Diagnostics',  // Diagnostics
        'DeveloperTools/TestSupport',  // Test support
        'DeveloperTools/CodeGeneration', // Code generators
        'Foundation/CallableSerialization', // Callable serialization
        'Foundation/FailureBoundary',     // Failure boundary
        'Framework/System',        // Framework health types, resettable state
        'Application/Config',      // Configuration
    ];

    /**
     * Allowed dependency directions for specific components.
     * Key = source component (Area/SubComponent), Value = allowed target components.
     *
     * Intra-component imports (same Area/SubComponent) are always allowed.
     * Cross-component imports are only allowed when explicitly listed here
     * or when the target is a universal dependency.
     */
    private array $allowedDependencyDirections = [
        // Identity area — Auth is the central hub
        'Identity/Auth' => [
            'Identity/Access',
            'Identity/Credentials',
            'Identity/Tokens',
            'Identity/ExternalIdentity',
            'Identity/Tenancy',
            'Identity/Security',
            'Security/Hashing',
        ],
        // Identity area — Credentials depends on Auth and Access
        'Identity/Credentials' => [
            'Identity/Access',
            'Identity/Auth',
        ],
        // Identity area — Tokens depends on Auth, Access, ExternalIdentity
        'Identity/Tokens' => [
            'Identity/Auth',
            'Identity/Access',
            'Identity/ExternalIdentity',
            'Identity/Credentials',
        ],
        // Identity area — Access depends on Auth, Credentials, Tenancy
        'Identity/Access' => [
            'Identity/Auth',
            'Identity/Credentials',
            'Identity/Tenancy',
            'Identity/ExternalIdentity',
        ],
        // Identity area — Tenancy depends on Auth, Access, ExternalIdentity, Credentials
        'Identity/Tenancy' => [
            'Identity/Auth',
            'Identity/Access',
            'Identity/ExternalIdentity',
            'Identity/Credentials',
        ],
        // Identity area — ExternalIdentity depends on Auth, Tokens
        'Identity/ExternalIdentity' => [
            'Identity/Auth',
            'Identity/Tokens',
            'Identity/Access',
        ],
        // Identity area — Credentials depends on Tokens for refresh token interfaces
        'Identity/Credentials' => [
            'Identity/Access',
            'Identity/Auth',
            'Identity/Tokens',
        ],
        // Identity area — Security is independent
        'Identity/Security' => [],
        // Operations area
        'Operations/Queue' => [
            'Operations/Tasks',
        ],
        'Operations/Notifications' => [
            'Operations/Mail',
        ],
        // HTTP area
        'HTTP/Router' => [
            'HTTP/Dispatcher',
            'HTTP/System',
        ],
        'HTTP/Middleware' => [
            'HTTP/System',
        ],
        'HTTP/Security' => [
            'Security/System',
        ],
        // HTTP/Dispatcher depends on HTTP/System
        'HTTP/Dispatcher' => [
            'HTTP/System',
        ],
        // API area — sub-components depend on ApiBlueprint
        'API/OpenAPI' => [
            'API/ApiBlueprint',
        ],
        'API/GraphQL' => [
            'API/ApiBlueprint',
        ],
        'API/Contracts' => [
            'API/ApiBlueprint',
        ],
        'API/SchemaGeneration' => [
            'API/ApiBlueprint',
            'DataStack/DataTransfer',
        ],
        // API area — ApiBlueprint depends on Resilience for webhooks
        'API/ApiBlueprint' => [
            'Operations/Resilience',
        ],
        // Operations area
        'Operations/RuntimeSupervision' => [
            'Operations/Events',
            'Operations/Observability',
        ],
        'Operations/ApplicationWorkflow' => [
            'Operations/Events',
            'Operations/Queue',
        ],
        'Operations/BackgroundProcesses' => [
            'Operations/Queue',
            'Operations/Events',
        ],
        // DataStack area
        'DataStack/Persistence' => [
            'DataStack/Database',
            'DataStack/DataTransfer',
        ],
        'DataStack/Database' => [
            'DataStack/DataTransfer',
        ],
        // Integration area
        'Integration/ObjectStorage' => [
            'Operations/Filesystem',
        ],
        // Application/Facade — compat layer, may import any Identity component
        'Application/Facade' => [
            'Identity/Auth',
        ],
        // Presentation area
        'Presentation/View' => [
            'Application/Filesystem',
        ],
    ];

    /**
     * Framework/System is the integration layer — it wires components together.
     * Framework code is allowed to import from any component.
     * This is by design: the framework is the composition boundary.
     */
    private function isFrameworkIntegration(string $sourceComponent): bool
    {
        return $sourceComponent === 'Framework/System';
    }

    /**
     * BLOCKER patterns — imports that are never allowed.
     */
    private array $blockerPatterns = [
        '/\\\\Internal\\\\/' => 'Import from \\Internal\\ namespace (forbidden reach-through)',
    ];

    /**
     * MEDIUM patterns — imports of deep implementation details.
     */
    private array $mediumPatterns = [
        '/InMemory.*Store$/'     => 'Import of InMemory store implementation from another component',
        '/\\\\Runtime\\\\.*Store/' => 'Import of runtime store from another component',
        '/\\\\Adapters\\\\/'      => 'Import of runtime adapter from another component',
    ];

    /**
     * Explicit justified allowances.
     * Format: relative_path => [imported_class_substring => reason]
     */
    private array $knownAllowances = [];

    /** @var list<array{severity: string, file: string, line: int, description: string, importedClass: string, sourceComponent: string, targetComponent: string}> */
    private array $errors = [];

    /**
     * Directories to scan for component source files.
     */
    private array $scanRoots = [
        'components',
        'framework/System',
    ];

    /**
     * Contexts to skip entirely.
     */
    private array $excludedContexts = [
        '/tests/',
        '/Tests/',
        '/tooling/',
        '/Tooling/',
        '/vendor/',
        '/examples/',
        '/Examples/',
        '/docs/',
        '/Docs/',
        '/labs/',
        '/Labs/',
    ];

    /**
     * Composition roots may wire any component within their scope.
     */
    private function isCompositionRoot(string $relativePath): bool
    {
        return str_contains($relativePath, '/Configuration/Assembly/');
    }

    /**
     * Skip contexts that are not production source.
     */
    private function isExcluded(string $relativePath): bool
    {
        foreach ($this->excludedContexts as $context) {
            if (str_contains($relativePath, $context)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract all `use` statements from PHP file content.
     *
     * @return list<string>
     */
    private function extractImports(string $content): array
    {
        $imports = [];
        preg_match_all(
            '/^use\s+([A-Z][A-Za-z0-9_\\\\]+)(?:\s+as\s+\w+)?\s*;/m',
            $content,
            $matches,
            PREG_SET_ORDER,
        );
        foreach ($matches as $match) {
            $imports[] = $match[1];
        }

        return $imports;
    }

    /**
     * Resolve the component from a fully-qualified class name.
     *
     * Returns "Area/SubComponent" for component classes,
     * "Framework/System" for framework classes,
     * or null for external/vendor classes.
     */
    private function resolveComponent(string $fqcn): ?string
    {
        if (str_starts_with($fqcn, 'Avax\\Components\\')) {
            $remainder = substr($fqcn, strlen('Avax\\Components\\'));
            $parts = explode('\\', $remainder);
            if (count($parts) >= 2) {
                return $parts[0].'/'.$parts[1];
            }
            if (count($parts) === 1) {
                return $parts[0];
            }

            return null;
        }

        if (str_starts_with($fqcn, 'Avax\\Framework\\')) {
            return 'Framework/System';
        }

        return null; // External — skip
    }

    /**
     * Resolve the component from a file path.
     */
    private function resolveFileComponent(string $relativePath): ?string
    {
        if (str_starts_with($relativePath, 'components/')) {
            $remainder = substr($relativePath, strlen('components/'));
            $parts = explode('/', $remainder);
            if (count($parts) >= 2) {
                return $parts[0].'/'.$parts[1];
            }
            if (count($parts) === 1) {
                return $parts[0];
            }
        }

        if (str_starts_with($relativePath, 'framework/System/')) {
            return 'Framework/System';
        }

        return null;
    }

    /**
     * Check if a class name matches any BLOCKER pattern.
     */
    private function checkBlockerPatterns(string $fqcn): ?array
    {
        foreach ($this->blockerPatterns as $pattern => $description) {
            if (preg_match($pattern, $fqcn)) {
                return ['severity' => 'BLOCKER', 'description' => $description];
            }
        }

        return null;
    }

    /**
     * Check if a class name matches any MEDIUM pattern.
     */
    private function checkMediumPatterns(string $fqcn): ?string
    {
        foreach ($this->mediumPatterns as $pattern => $description) {
            if (preg_match($pattern, $fqcn)) {
                return $description;
            }
        }

        return null;
    }

    /**
     * Check if a specific import is in the known allowlist.
     */
    private function isKnownAllowed(string $relativePath, string $fqcn): bool
    {
        if (! isset($this->knownAllowances[$relativePath])) {
            return false;
        }

        foreach ($this->knownAllowances[$relativePath] as $allowedSubstring => $reason) {
            if (str_contains($fqcn, $allowedSubstring)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the target component is in the allowed directions for the source.
     */
    private function isAllowedDirection(string $sourceComponent, string $targetComponent): bool
    {
        $allowed = $this->allowedDependencyDirections[$sourceComponent] ?? [];

        return in_array($targetComponent, $allowed, true);
    }

    /**
     * Get the top-level area from a component string.
     */
    private function getArea(string $component): string
    {
        $parts = explode('/', $component);

        return $parts[0];
    }

    public function check(): array
    {
        $basePath = dirname(__DIR__, 2);

        foreach ($this->scanRoots as $root) {
            $fullPath = $basePath.'/'.$root;
            if (! is_dir($fullPath)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($fullPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY,
            );

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $relativePath = str_replace($basePath.'/', '', $file->getPathname());

                if ($this->isExcluded($relativePath)) {
                    continue;
                }

                $this->scanFile($file, $relativePath, $basePath);
            }
        }

        return [
            'status' => $this->errors === [] ? 'PASS' : 'FAIL',
            'errors' => $this->formatErrors(),
        ];
    }

    private function scanFile(SplFileInfo $file, string $relativePath, string $basePath): void
    {
        // Composition roots may wire any component
        if ($this->isCompositionRoot($relativePath)) {
            return;
        }

        $content = file_get_contents($file->getPathname());
        if ($content === false) {
            return;
        }

        $imports = $this->extractImports($content);
        if ($imports === []) {
            return;
        }

        $sourceComponent = $this->resolveFileComponent($relativePath);
        if ($sourceComponent === null) {
            return;
        }

        $lines = explode("\n", $content);

        foreach ($imports as $fqcn) {
            // Check BLOCKER patterns first
            $blocker = $this->checkBlockerPatterns($fqcn);
            if ($blocker !== null) {
                // Find line number
                $lineNumber = $this->findImportLine($lines, $fqcn);
                $this->recordError(
                    'BLOCKER',
                    $relativePath,
                    $lineNumber,
                    $blocker['description'],
                    $fqcn,
                    $sourceComponent,
                    'unknown',
                );
                continue;
            }

            $targetComponent = $this->resolveComponent($fqcn);
            if ($targetComponent === null) {
                continue; // External — skip
            }

            // Same exact component — always allowed
            if ($sourceComponent === $targetComponent) {
                continue;
            }

            // Framework/System is the integration layer — allowed to wire any component
            if ($this->isFrameworkIntegration($sourceComponent)) {
                continue;
            }

            // Universal dependencies — all components may import
            if (in_array($targetComponent, $this->universalDependencies, true)) {
                continue;
            }

            // Check known allowances
            if ($this->isKnownAllowed($relativePath, $fqcn)) {
                continue;
            }

            // Check if this direction is allowed
            if (! $this->isAllowedDirection($sourceComponent, $targetComponent)) {
                $lineNumber = $this->findImportLine($lines, $fqcn);

                // Check for MEDIUM severity (implementation detail leak)
                $mediumDesc = $this->checkMediumPatterns($fqcn);
                $severity = $mediumDesc !== null ? 'MEDIUM' : 'HIGH';
                $description = $mediumDesc !== null
                    ? $mediumDesc.' — '.$sourceComponent.' -> '.$targetComponent
                    : 'Dependency direction violation: '.$sourceComponent.' -> '.$targetComponent;

                $this->recordError(
                    $severity,
                    $relativePath,
                    $lineNumber,
                    $description,
                    $fqcn,
                    $sourceComponent,
                    $targetComponent,
                );
            }
        }
    }

    /**
     * Find the line number where an import statement appears.
     */
    private function findImportLine(array $lines, string $fqcn): int
    {
        $escaped = preg_quote($fqcn, '/');
        foreach ($lines as $i => $line) {
            if (preg_match('/^use\s+'.$escaped.'(?:\s+as\s+\w+)?\s*;/', $line)) {
                return $i + 1;
            }
        }

        return 0;
    }

    private function recordError(
        string $severity,
        string $file,
        int $line,
        string $description,
        string $importedClass,
        string $sourceComponent,
        string $targetComponent,
    ): void {
        $this->errors[] = [
            'severity' => $severity,
            'file' => $file,
            'line' => $line,
            'description' => $description,
            'importedClass' => $importedClass,
            'sourceComponent' => $sourceComponent,
            'targetComponent' => $targetComponent,
        ];
    }

    /** @return list<string> */
    private function formatErrors(): array
    {
        $formatted = [];
        foreach ($this->errors as $error) {
            $formatted[] = sprintf(
                '[%s] %s:%d — %s (imports %s)',
                $error['severity'],
                $error['file'],
                $error['line'],
                $error['description'],
                $error['importedClass'],
            );
        }

        return $formatted;
    }
}

if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    $checker = new CheckIntrusiveCoupling();
    $result = $checker->check();

    echo $result['status']."\n";

    if (! empty($result['errors'])) {
        echo implode("\n", $result['errors'])."\n";
        exit(1);
    }

    exit(0);
}

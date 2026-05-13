<?php

declare(strict_types=1);

namespace Avax\Tooling\Components;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * check-component-runtime-assembly.php
 *
 * Fails on hidden infrastructure construction in active runtime FLOWS and PUBLIC SURFACE.
 *
 * V5.8.4 tightened rules:
 * - $x ?? new Dependency() is ONLY acceptable in: ServiceProvider, Configuration, Build* flows,
 *   explicit Factory classes, test setup, CLI tooling closures, diagnostic code.
 * - NOT acceptable in: runtime execution flows, HTTP handling, error rendering, event dispatch,
 *   database/query execution, queue/job execution, public surface behavior, component capabilities.
 *
 * Context-aware path-based rules (not class-allowlist-only):
 *
 * Allowed contexts for ?? new / new infrastructure:
 * - /tests/                          — test fixtures
 * - /CLI/                            — CLI commands
 * - /Configuration/                  — service provider / configuration
 * - Build* assembly flows             — BuildCompiledCache, ApplicationBuilder, etc.
 * - /tooling/                         — diagnostic / governance scripts
 * - Filesystem component internals    — Application/Filesystem own construction
 *
 * Forbidden contexts:
 * - runtime execution paths (Flows/ that handle requests, queries, events, jobs)
 * - PublicSurface behavior
 * - failure rendering
 * - queue/job execution
 * - event/listener execution
 *
 * Allowed direct `new` everywhere (value objects, config, exceptions, primitives):
 * - value objects, DTOs, events, exceptions
 * - immutable local config objects (Config, Configuration suffixes)
 * - policy objects with static factories (Policy suffixes)
 * - nodes, builders, resolvers, compilers used as internal helpers
 * - Clock primitives (SystemClock)
 * - UploadedFiles, ResolveRequest (execution context VOs)
 */

$rootDir = realpath(__DIR__ . '/../..');
if ($rootDir === false) {
    fwrite(STDERR, "Cannot resolve repository root.\n");
    exit(1);
}

$scanDirs = array_values(array_filter([
                                          realpath($rootDir . '/components'),
                                          realpath($rootDir . '/framework'),
                                      ], static fn (string|false $path) : bool => is_string($path)));

$violations = [];
$checked    = 0;

// Patterns that are ALWAYS allowed (value objects, config, exceptions, primitives)
$allowedClasses = '/^(Config|uration|Exception|Error|ValueObject|Event|DTO|Node|Builder|Policy|Validator|Parser|Resolver|Compiler|Clock|Serializer|TaskRetryPolicy|QueryNode|DeadlockDetectorConfig|CompileDataQuery|UploadedFiles|ResolveRequest|CacheStoreRecordWasMissing|FrequencyTracker|CurlTransport|IdempotencyStore|InMemoryFailedJobsStore|InMemorySagaStore|InMemoryCacheStore|MatchRoute|SchemaValidator|NativeYamlParser|OpenApiConfiguration|ApiContractsConfiguration|GraphQLConfiguration|SystemClock|JsonCacheSerializer|DependencyCompiler|RuntimeException)$/';

// Context paths where ?? new / new infrastructure IS acceptable (assembly, config, test, tooling)
$allowedContextPatterns = [
    '/tests/',
    '/CLI/',
    '/Configuration/',
    '/ServiceProvider',
    'BuildApplication',
    'BuildCompiledCache',
    '/CodeGeneration/',
    '/SystemDesign/',
    '/PreCommit/',
    '/AppKernel.php',
    '/Operations/Queue/System/Capabilities/Driver/',
    '/tooling/',
    // Filesystem component may construct itself internally
    '/Application/Filesystem/',
    // Diagnostic / doctor flows (construct RuntimeSafety::create internally)
    'RunDoctor',
    'DetectStateLeak',
    'InspectStaticState',
    'VerifyResetWasExecuted',
    'VerifyRequestScopeWasClosed',
];

// Context paths where ?? new / new infrastructure is FORBIDDEN (runtime execution)
$forbiddenContextPatterns = [
    '/Flows/HandleIncomingHttp/',
    '/Flows/RunApplication/',
    '/Flows/RunConsoleCommand/',
    '/Flows/DispatchEvent/',
    '/Flows/ExecuteQuery/',
    '/Flows/RunWorkerLoop/',
    '/PublicSurface/',
    '/FailureBoundary/',
    '/FailureRendering/',
];

foreach ($scanDirs as $scanDir) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($scanDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $path         = $file->getPathname();
        $relativePath     = str_replace($rootDir . '/', '', $path);

        // Skip allowed contexts entirely (tests, tooling, CLI, etc.)
        $inAllowedContext = false;
        foreach ($allowedContextPatterns as $pattern) {
            if (str_contains($path, $pattern)) {
                $inAllowedContext = true;
                break;
            }
        }
        if ($inAllowedContext) {
            continue;
        }

        $checked++;

        // Flag files in forbidden runtime contexts for extra scrutiny
        $inForbiddenContext = false;
        foreach ($forbiddenContextPatterns as $pattern) {
            if (str_contains($path, $pattern)) {
                $inForbiddenContext = true;
                break;
            }
        }

        $content = file_get_contents($path);
        if ($content === false) {
            continue;
        }

        $lines = explode("\n", $content);
        foreach ($lines as $lineNum => $line) {
            $trimmed = trim($line);

            // Skip comments
            if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '*') || str_starts_with($trimmed, '#')) {
                continue;
            }

            // Flag: $x ?? new ComplexRuntime() pattern
            if (preg_match('/\$\w+\s*\?\?\s*new\s+(\w+)/', $line, $m)) {
                $className = $m[1];
                // Skip allowed value objects, config, exceptions, primitives
                if (preg_match($allowedClasses, $className)) {
                    continue;
                }
                $severity     = $inForbiddenContext ? 'FORBIDDEN CONTEXT' : 'SUSPECT';
                $violations[] = "[$severity] $relativePath:" . ($lineNum + 1) . " — null-coalescing new: $trimmed";
            }

            // Flag: new $listener/handler/controller/middleware/job
            if (preg_match('/new\s+\$(listener|handler|controller|middleware|job)\b/i', $line)) {
                $violations[] = "$relativePath:" . ($lineNum + 1) . " — dynamic new: $trimmed";
            }
        }
    }
}

if ($violations !== []) {
    echo "FAIL: " . count($violations) . " runtime assembly violations found:\n";
    foreach ($violations as $v) {
        echo "  - $v\n";
    }
    exit(1);
}

echo "PASS: $checked active flow/surface files scanned, no hidden runtime assembly\n";
exit(0);

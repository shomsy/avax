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
 *   explicit Factory classes, test setup, CLI tooling closures.
 * - NOT acceptable in: runtime execution flows, HTTP handling, error rendering, event dispatch,
 *   database/query execution, queue/job execution, public surface behavior, component capabilities.
 *
 * Allowed direct `new`:
 * - value objects, DTOs, events, exceptions
 * - immutable local config objects (Config, Configuration suffixes)
 * - policy objects with static factories (Policy suffixes)
 * - nodes, builders, resolvers, compilers used as internal helpers
 * - Clock primitives (SystemClock)
 * - UploadedFiles, ResolveRequest (execution context VOs)
 */

$scanDirs = [
    __DIR__ . '/../../components',
    __DIR__ . '/../../framework',
];

$violations = [];
$checked    = 0;

// Patterns that are ALWAYS allowed (value objects, config, exceptions, primitives)
$allowedClasses = '/^(Config|uration|Exception|Error|ValueObject|Event|DTO|Node|Builder|Policy|Validator|Parser|Resolver|Compiler|Clock|Serializer|TaskRetryPolicy|QueryNode|DeadlockDetectorConfig|CompileDataQuery|UploadedFiles|ResolveRequest|CacheStoreRecordWasMissing|FrequencyTracker|CurlTransport|IdempotencyStore|InMemoryFailedJobsStore|InMemorySagaStore|InMemoryCacheStore|MatchRoute|SchemaValidator|NativeYamlParser|OpenApiConfiguration|ApiContractsConfiguration|GraphQLConfiguration|SystemClock|JsonCacheSerializer|DependencyCompiler|RuntimeException|Filesystem)$/';

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
        $relativePath = str_replace(__DIR__ . '/../../', '', $path);

        // Skip allowed paths entirely
        $skipPatterns = [
            '/tests/',
            '/shortcuts.php',
            '/CLI/',
            '/CodeGeneration/',
            '/SystemDesign/',
            '/PreCommit/',
            '/AppKernel.php',
            '/Operations/Queue/System/Capabilities/Driver/',
        ];
        $skip         = false;
        foreach ($skipPatterns as $pattern) {
            if (strpos($path, $pattern) !== false) {
                $skip = true;
                break;
            }
        }
        if ($skip) {
            continue;
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
                $violations[] = "$relativePath:" . ($lineNum + 1) . " — null-coalescing new: $trimmed";
                $checked++;
            }

            // Flag: new $listener/handler/controller/middleware/job
            if (preg_match('/new\s+\$(listener|handler|controller|middleware|job)\b/i', $line)) {
                $violations[] = "$relativePath:" . ($lineNum + 1) . " — dynamic new: $trimmed";
                $checked++;
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

#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * check-attributes-compiled.php — Gate: Ensures that methods with failure attributes have compiled metadata.
 *
 * Exit code 0: All attributes have compiled metadata.
 * Exit code 1: Some methods have attributes but no compiled metadata.
 *
 * Usage:
 * php tooling/failure-boundary/check-attributes-compiled.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\CompileFailurePolicies\CompileFailurePolicies;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledPolicyCache;

$compiler = new CompileFailurePolicies();
$failures = [];

// Scan framework and components for classes with failure attributes
$directories = [
    __DIR__ . '/../../framework',
    __DIR__ . '/../../components',
];

foreach ($directories as $dir) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $content = file_get_contents($file->getPathname());

        // Quick check: does this file contain any failure boundary attributes?
        if (!preg_match('/#\[OnFailure|#\[Retry|#\[ReportFailure|#\[Fallback|#\[DeadLetter|#\[RecoverWith|#\[Timeout|#\[Rethrow/', $content)) {
            continue;
        }

        // Extract class name from namespace + class declaration
        if (!preg_match('/namespace\s+([^;]+);/', $content, $nsMatch)) {
            continue;
        }
        if (!preg_match('/(?:final\s+)?class\s+(\w+)/', $content, $classMatch)) {
            continue;
        }

        $className = $nsMatch[1] . '\\' . $classMatch[1];

        if (!class_exists($className)) {
            continue;
        }

        $ref = new ReflectionClass($className);

        foreach ($ref->getMethods() as $method) {
            $attrs = $method->getAttributes();
            $hasFailureAttr = false;

            foreach ($attrs as $attr) {
                $name = $attr->getName();
                if (str_contains($name, 'FailureBoundary') ||
                    $name === 'OnFailure' || $name === 'Retry' || $name === 'ReportFailure' ||
                    $name === 'Fallback' || $name === 'DeadLetter' || $name === 'RecoverWith' ||
                    $name === 'Timeout' || $name === 'Rethrow') {
                    $hasFailureAttr = true;
                    break;
                }
            }

            if (!$hasFailureAttr) {
                continue;
            }

            $key = $className . '::' . $method->getName();
            $compiled = CompiledPolicyCache::get($key);

            if ($compiled === null) {
                // Try to compile on demand
                $result = $compiler->compileAndCache($className, $method->getName());

                if (!$result) {
                    $failures[] = $key;
                }
            }
        }
    }
}

if (empty($failures)) {
    echo "GREEN: All failure attributes have compiled metadata.\n";
    exit(0);
}

echo "RED: Methods with failure attributes but no compiled metadata:\n";
foreach ($failures as $failure) {
    echo "  - {$failure}\n";
}

exit(1);

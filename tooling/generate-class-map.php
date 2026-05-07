<?php

declare(strict_types=1);

namespace Avax\Tooling;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

$root = getcwd();
$map = [];

$dirs = [
    'framework/System' => 'Avax\\Framework\\System',
    'components' => 'Avax\\Components',
];

foreach ($dirs as $base => $rootNamespace) {
    $path = $root.'/'.$base;
    if (! is_dir($path)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST,
    );

    foreach ($iterator as $file) {
        if (! $file->isFile()) {
            continue;
        }

        if ($file->getExtension() !== 'php') {
            continue;
        }

        $rel = substr((string) $file->getPathname(), strlen($root) + 1);
        $content = file_get_contents($file->getPathname());

        if (! preg_match('/^namespace\s+([^;]+);/m', $content, $m)) {
            continue;
        }

        $namespace = trim($m[1]);

        if (! preg_match(
            '/^(?:final|abstract|readonly\s+final|readonly\s+abstract|final\s+readonly|abstract\s+readonly)?\s*(?:class|interface|trait)\s+(\w+)/m',
            $content,
            $c,
        )) {
            continue;
        }

        $className = $c[1];
        $fqcn = $namespace.'\\'.$className;

        $parts = explode('/', $rel);
        $lane = 'unknown';

        if ($base === 'framework/System') {
            // framework/System/Lane/...
            if (count($parts) >= 3) {
                $lane = $parts[2];
            }
        } else {
            // components/Area/Component/System/Lane/...
            // or components/Area/Component/Lane/... (some might lack System folder if they are simple?)
            // No, AGENTS.md says System/ is required.

            $systemIndex = array_search('System', $parts);
            if ($systemIndex !== false && isset($parts[$systemIndex + 1])) {
                $lane = $parts[$systemIndex + 1];
                // Check if the lane is actually a file (then it's a root class in System/)
                if (str_ends_with($lane, '.php')) {
                    $lane = 'SystemRoot';
                }
            }
        }

        $status = 'canonical';
        if (str_contains($content, '@internal')) {
            $status = 'internal';
        }

        if (str_contains($content, '@experimental')) {
            $status = 'experimental';
        }

        if (str_contains($content, '@deprecated')) {
            $status = 'deprecated-bridge';
        }

        $suite = '';
        $component = '';
        if (str_starts_with($rel, 'components')) {
            $suite = $parts[1] ?? '';
            $component = $parts[2] ?? '';
        } else {
            $suite = 'Framework';
            $component = 'Core';
        }

        $map[$fqcn] = [
            'fqcn' => $fqcn,
            'file' => $rel,
            'suite' => $suite,
            'component' => $component,
            'lane' => $lane,
            'status' => $status,
        ];
    }
}

$json = json_encode(array_values($map), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
file_put_contents($root.'/build/canonical-class-map.json', $json);

echo 'Generated '.count($map)." class entries\n";
echo 'Output: build/canonical-class-map.json'.PHP_EOL;

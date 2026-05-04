<?php

declare(strict_types=1);

namespace Avax\Tooling;
$root = getcwd();
$map = [];

$dirs = [
    'framework/System' => 'Avax\\Framework\\System',
    'components' => 'Avax\\Components',
];

foreach (array_keys($dirs) as $base) {
    $path = $root . '/' . $base;
    if (!is_dir($path)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST,
    );

    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }

        if ($file->getExtension() !== 'php') {
            continue;
        }

        $rel = substr((string)$file->getPathname(), strlen($root) + 1);
        $content = file_get_contents($file->getPathname());

        if (!preg_match('/^namespace\s+([^;]+);/m', $content, $m)) {
            continue;
        }

        $namespace = trim($m[1]);

        if (!preg_match(
            '/^(?:final|abstract)\s+(?:readonly\s+)?(?:class|interface|trait)\s+(\w+)/m',
            $content,
            $c,
        )) {
            continue;
        }

        $className = $c[1];
        $fqcn = $namespace . '\\' . $className;

        $parts = explode('/', $rel);
        $lane = 'unknown';
        if (count($parts) >= 3 && $parts[count($parts) - 3] === 'System') {
            $lane = $parts[count($parts) - 2];
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
        if ($base === 'components') {
            $suite = $parts[1] ?? '';
            $component = $parts[2] ?? '';
        } else {
            $suite = 'Framework';
            $component = $parts[2] ?? '';
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
file_put_contents($root . '/build/canonical-class-map.json', $json);

echo 'Generated ' . count($map) . " class entries\n";
echo 'Output: build/canonical-class-map.json' . PHP_EOL;
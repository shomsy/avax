<?php

namespace Avax\Tooling\Quality;

$output = shell_exec('composer dump-autoload -o 2>&1 2>&1');
$lines = explode("\n", $output);

$issues = [
    'multi_class' => [],
    'namespace_mismatch' => [],
    'wrong_path' => [],
];

foreach ($lines as $line) {
    if (! str_contains($line, 'Skipping')) {
        continue;
    }

    if (preg_match('/located in \.\/(.+?) does not comply/', $line, $m)) {
        $file = $m[1];

        if (preg_match('/(Test|Fake|Mock|Stub|Benchmark)/', $file)) {
            continue;
        }

        $content = file_get_contents($basePath = dirname(__DIR__, 2).'/'.$file);
        $namespace = null;
        if (preg_match('/^namespace\s+([A-Za-z\\\\]+);/m', $content, $ns)) {
            $namespace = $ns[1];
        }

        $classCount = preg_match_all('/^(final |abstract )?(class|interface|trait|enum)\s+/m', $content);

        if ($classCount > 1) {
            $issues['multi_class'][] = $file.sprintf(' (%d classes)', $classCount);
        } elseif ($namespace) {
            $expectedNs = 'Avax\\Components\\'.dirname($file);
            $expectedNs = str_replace('/', '\\', $expectedNs);

            if (str_replace('\\', '', $namespace) !== str_replace('\\', '', $expectedNs)) {
                $issues['namespace_mismatch'][] = $file.(' -> '.$namespace);
            }
        }
    }
}

echo "=== Autoload Issues Analysis ===\n\n";

echo 'Multi-class files ('.count($issues['multi_class'])."):\n";
foreach (array_slice($issues['multi_class'], 0, 15) as $i) {
    echo sprintf('  - %s%s', $i, PHP_EOL);
}

if (count($issues['multi_class']) > 15) {
    echo '  ... and '.(count($issues['multi_class']) - 15)." more\n";
}

echo "\nNamespace mismatches (".count($issues['namespace_mismatch'])."):\n";
foreach (array_slice($issues['namespace_mismatch'], 0, 10) as $i) {
    echo sprintf('  - %s%s', $i, PHP_EOL);
}

echo "\nTotal unique files: ".(count($issues['multi_class']) + count($issues['namespace_mismatch']))."\n";

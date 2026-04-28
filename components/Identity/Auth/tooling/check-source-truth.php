<?php

declare(strict_types=1);

use Avax\Auth\Integrations\Release\CheckSourceTruth;

require dirname(path: __DIR__) . '/vendor/autoload.php';

$root   = dirname(path: __DIR__);
$json   = in_array(needle: '--json', haystack: $argv, strict: true) || in_array(needle: '-j', haystack: $argv, strict: true);
$result = new CheckSourceTruth()->execute(repositoryRoot: $root);

if ($json) {
    echo json_encode(value: $result, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit($result['approved'] ? 0 : 1);
}

echo "=== Auth Source Truth Check ===\n";
echo 'Status: ' . ($result['approved'] ? 'APPROVED' : 'ISSUES') . "\n";

foreach ($result['checked'] as $name => $present) {
    echo sprintf("  - %s: %s\n", $name, $present ? 'present' : 'missing');
}

if ($result['issues'] !== []) {
    echo "Issues:\n";

    foreach ($result['issues'] as $issue) {
        echo '  - ' . $issue . "\n";
    }
}

exit($result['approved'] ? 0 : 1);

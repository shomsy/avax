<?php

declare(strict_types=1);

use Avax\Auth\Integrations\Release\CheckSourceTruth;

require dirname(__DIR__) . '/vendor/autoload.php';

$root   = dirname(__DIR__);
$json   = in_array('--json', $argv, true) || in_array('-j', $argv, true);
$result = (new CheckSourceTruth())->execute(repositoryRoot: $root);

if ($json) {
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
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

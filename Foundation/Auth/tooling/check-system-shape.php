<?php

declare(strict_types=1);

use Avax\Auth\Integrations\Release\CheckSystemShape;

require dirname(__DIR__) . '/vendor/autoload.php';

$root   = dirname(__DIR__);
$json   = in_array('--json', $argv, true) || in_array('-j', $argv, true);
$result = new CheckSystemShape()->execute(repositoryRoot: $root);

if ($json) {
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit($result['approved'] ? 0 : 1);
}

echo "=== Auth System Shape Check ===\n";
echo 'Status: ' . ($result['approved'] ? 'APPROVED' : 'ISSUES') . "\n";

if ($result['unexpected_top_level'] !== []) {
    echo "Unexpected top-level entries:\n";

    foreach ($result['unexpected_top_level'] as $path) {
        echo '  - ' . $path . "\n";
    }
}

if ($result['forbidden_directories'] !== []) {
    echo "Forbidden directories:\n";

    foreach ($result['forbidden_directories'] as $path) {
        echo '  - ' . $path . "\n";
    }
}

if ($result['issues'] !== []) {
    echo "Issues:\n";

    foreach ($result['issues'] as $issue) {
        echo '  - ' . $issue . "\n";
    }
}

exit($result['approved'] ? 0 : 1);

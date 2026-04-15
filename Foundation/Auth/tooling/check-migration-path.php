<?php

declare(strict_types=1);

use Avax\Auth\Integrations\Release\CheckMigrationPath;

require dirname(__DIR__) . '/vendor/autoload.php';

$root   = dirname(__DIR__);
$json   = in_array('--json', $argv, true) || in_array('-j', $argv, true);
$result = (new CheckMigrationPath())->execute(repositoryRoot: $root);

if ($json) {
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit($result['clean'] ? 0 : 1);
}

echo "=== Auth Migration Path Check ===\n";
echo 'Package: ' . $result['package'] . "\n";
echo 'Namespace: ' . $result['current_namespace'] . "\n";
echo 'Migration documented: ' . ($result['migration_documented'] ? 'yes' : 'no') . "\n";
echo 'Upgrade test present: ' . ($result['automated_upgrade_test'] ? 'yes' : 'no') . "\n";
echo 'Status: ' . ($result['clean'] ? 'CLEAN' : 'WARN') . "\n";

if ($result['legacy_namespace_references'] !== []) {
    echo "Legacy references:\n";

    foreach ($result['legacy_namespace_references'] as $reference) {
        echo '  - ' . $reference . "\n";
    }
}

if ($result['notes'] !== []) {
    echo "Notes:\n";

    foreach ($result['notes'] as $note) {
        echo '  - ' . $note . "\n";
    }
}

exit($result['clean'] ? 0 : 1);

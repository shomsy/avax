<?php

declare(strict_types=1);

use Avax\Components\Identity\Auth\Integrations\Release\CheckMigrationPath;

require dirname(path: __DIR__) . '/vendor/autoload.php';

$root   = dirname(path: __DIR__);
$json   = in_array(needle: '--json', haystack: $argv, strict: true) || in_array(needle: '-j', haystack: $argv, strict: true);
$result = new CheckMigrationPath()->execute(repositoryRoot: $root);

if ($json) {
    echo json_encode(value: $result, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
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

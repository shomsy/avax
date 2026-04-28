<?php

declare(strict_types=1);

use Avax\Auth\Integrations\Release\ScanCommittedSecrets;

require dirname(path: __DIR__) . '/vendor/autoload.php';

$root     = dirname(path: __DIR__);
$scanner  = new ScanCommittedSecrets();
$findings = $scanner->execute(rootPath: $root);

echo json_encode(
        value: [
                   'findings' => $findings,
                   'count'    => count(value: $findings),
               ],
        flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    ) . PHP_EOL;

exit($findings === [] ? 0 : 1);

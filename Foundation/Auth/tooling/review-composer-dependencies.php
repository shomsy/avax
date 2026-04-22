<?php

declare(strict_types=1);

use Avax\Auth\Integrations\Release\ReviewComposerDependencies;

require dirname(path: __DIR__) . '/vendor/autoload.php';

$root   = dirname(path: __DIR__);
$review = new ReviewComposerDependencies()->execute(
    composerLockPath: $root . '/composer.lock',
    policyPath      : $root . '/tooling/dependency-review-policy.json'
);

echo json_encode(
        value: $review,
        flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    ) . PHP_EOL;

exit($review['approved'] ? 0 : 1);

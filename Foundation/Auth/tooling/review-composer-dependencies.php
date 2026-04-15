<?php

declare(strict_types=1);

use Avax\Auth\Integrations\Release\ReviewComposerDependencies;

require dirname(__DIR__) . '/vendor/autoload.php';

$root   = dirname(__DIR__);
$review = (new ReviewComposerDependencies())->execute(
    composerLockPath: $root . '/composer.lock',
    policyPath      : $root . '/tooling/dependency-review-policy.json'
);

echo json_encode(
        $review,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    ) . PHP_EOL;

exit($review['approved'] ? 0 : 1);

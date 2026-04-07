<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DependencyInjection\Policies\ResolutionPolicy;

$strict = new ResolutionPolicy(strict: true, debug: false);
$relaxed = new ResolutionPolicy(strict: false, debug: false);

assertTrue($strict->isAllowed(DateTimeImmutable::class), 'Strict policy should allow instantiable classes.');
assertTrue($strict->isAllowed(Countable::class), 'Strict policy should allow interfaces.');
assertTrue(! $strict->isAllowed('custom-alias'), 'Strict policy should reject unknown aliases.');
assertTrue($relaxed->isAllowed('custom-alias'), 'Relaxed policy should allow unknown aliases.');

echo basename(__FILE__) . " ok\n";

<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, levels: 2) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Resolution\ResolutionPolicy;

$strict  = new ResolutionPolicy(strict: true, debug: false);
$relaxed = new ResolutionPolicy(strict: false, debug: false);

assertTrue(condition: $strict->isAllowed(abstract: DateTimeImmutable::class), message: 'Strict policy should allow instantiable classes.');
assertTrue(condition: $strict->isAllowed(abstract: Countable::class), message: 'Strict policy should allow interfaces.');
assertTrue(condition: ! $strict->isAllowed(abstract: 'custom-alias'), message: 'Strict policy should reject unknown aliases.');
assertTrue(condition: $relaxed->isAllowed(abstract: 'custom-alias'), message: 'Relaxed policy should allow unknown aliases.');

echo basename(path: __FILE__) . " ok\n";

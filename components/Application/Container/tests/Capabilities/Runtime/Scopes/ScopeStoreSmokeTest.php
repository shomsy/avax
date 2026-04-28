<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, levels: 3) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Components\Application\Container\DI\Capabilities\Runtime\Scopes\ScopeStore;

$store  = new ScopeStore();
$scoped = new ArrayObject();

assertThrows(
    expectedClass: ContainerException::class,
    callback     : static fn () => $store->set(abstract: 'scoped', instance: $scoped),
    message      : 'Scope store should fail closed when storing scoped values without an active scope.'
);

$store->open();
$store->set(abstract: 'scoped', instance: $scoped);
assertSame(expected: $scoped, actual: $store->get(abstract: 'scoped'), message: 'Scoped values should be readable inside the active scope.');
$store->close();

assertSame(expected: null, actual: $store->get(abstract: 'scoped'), message: 'Scoped values should disappear after scope close.');

$store->terminate();
assertSame(expected: null, actual: $store->get(abstract: 'scoped'), message: 'Terminate should leave the scoped store empty.');

echo basename(path: __FILE__) . " ok\n";

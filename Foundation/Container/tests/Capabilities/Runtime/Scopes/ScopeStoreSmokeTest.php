<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Container\DI\Capabilities\Runtime\Scopes\ScopeStore;

$store  = new ScopeStore();
$scoped = new ArrayObject();

assertThrows(
    ContainerException::class,
    static fn () => $store->set('scoped', $scoped),
    'Scope store should fail closed when storing scoped values without an active scope.'
);

$store->open();
$store->set('scoped', $scoped);
assertSame($scoped, $store->get('scoped'), 'Scoped values should be readable inside the active scope.');
$store->close();

assertSame(null, $store->get('scoped'), 'Scoped values should disappear after scope close.');

$store->terminate();
assertSame(null, $store->get('scoped'), 'Terminate should leave the scoped store empty.');

echo basename(__FILE__) . " ok\n";

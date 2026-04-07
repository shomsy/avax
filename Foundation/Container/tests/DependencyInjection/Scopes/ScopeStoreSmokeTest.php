<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DependencyInjection\Scopes\ScopeStore;

$store = new ScopeStore();
$shared = new stdClass();
$scoped = new ArrayObject();

$store->share('shared', $shared);
assertSame($shared, $store->get('shared'), 'Shared values should be readable without a scope.');

assertThrows(
    \Avax\Container\Errors\ContainerException::class,
    static fn() => $store->set('scoped', $scoped),
    'Scope store should fail closed when storing scoped values without an active scope.'
);

$store->open();
$store->set('scoped', $scoped);
assertSame($scoped, $store->get('scoped'), 'Scoped values should be readable inside the active scope.');
$store->close();

assertSame(null, $store->get('scoped'), 'Scoped values should disappear after scope close.');

$store->terminate();
assertSame(null, $store->get('shared'), 'Terminate should clear shared values.');

echo basename(__FILE__) . " ok\n";

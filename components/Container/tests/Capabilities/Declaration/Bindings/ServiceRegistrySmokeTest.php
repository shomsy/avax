<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, levels: 3) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Declaration\Bindings\ServiceRegistry;

$registry = new ServiceRegistry();

$registry->bind(abstract: 'logger', concrete: DateTimeImmutable::class)->tag(tags: 'infra');
$deferred = $registry->defer(abstract: 'deferred.logger', concrete: ArrayObject::class);
$registry->singleton(abstract: 'cache', concrete: ArrayObject::class);
$registry->scoped(abstract: 'request', concrete: stdClass::class);
$registry->when(consumer: 'Consumer')->needs(abstract: 'logger')->give(implementation: DirectoryIterator::class);

assertTrue(condition: $registry->has(abstract: 'logger'), message: 'Service registry should store bindings.');
assertSame(expected: DateTimeImmutable::class, actual: $registry->get(abstract: 'logger')?->concrete, message: 'Bindings should keep their concrete target.');
assertTrue(condition: $deferred->deferred, message: 'Deferred registrations should be marked as deferred.');
assertSame(expected: ['logger'], actual: $registry->getTaggedIds(tag: 'infra'), message: 'Tags should resolve back to registered ids.');
assertSame(expected: DirectoryIterator::class, actual: $registry->getContextualMatch(consumer: 'Consumer', needs: 'logger'), message: 'Target overrides should resolve.');
assertTrue(condition: isset($registry->all()['cache']), message: 'Registry should expose stored registrations.');

assertThrows(
    expectedClass: LogicException::class,
    callback     : static function () use ($registry) : void {
        $registry->alias(alias: 'logger.alias', abstract: 'logger');
        $registry->alias(alias: 'logger', abstract: 'logger.alias');
    },
    message      : 'Alias cycles should fail fast at registration time.'
);

echo basename(path: __FILE__) . " ok\n";

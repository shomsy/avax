<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Declaration\Bindings\ServiceRegistry;

$registry = new ServiceRegistry();

$registry->bind('logger', DateTimeImmutable::class)->tag('infra');
$deferred = $registry->defer('deferred.logger', ArrayObject::class);
$registry->singleton('cache', ArrayObject::class);
$registry->scoped('request', stdClass::class);
$registry->when('Consumer')->needs('logger')->give(DirectoryIterator::class);

assertTrue($registry->has('logger'), 'Service registry should store bindings.');
assertSame(DateTimeImmutable::class, $registry->get('logger')?->concrete, 'Bindings should keep their concrete target.');
assertTrue($deferred->deferred, 'Deferred registrations should be marked as deferred.');
assertSame(['logger'], $registry->getTaggedIds('infra'), 'Tags should resolve back to registered ids.');
assertSame(DirectoryIterator::class, $registry->getContextualMatch('Consumer', 'logger'), 'Target overrides should resolve.');
assertTrue(isset($registry->all()['cache']), 'Registry should expose stored registrations.');

assertThrows(
    LogicException::class,
    static function () use ($registry) : void {
        $registry->alias('logger.alias', 'logger');
        $registry->alias('logger', 'logger.alias');
    },
    'Alias cycles should fail fast at registration time.'
);

echo basename(__FILE__) . " ok\n";

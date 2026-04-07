<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Capabilities\Prototypes\Factory;

use Avax\Container\DependencyInjection\Capabilities\Prototypes\Analyze\PrototypeAnalyzer;
use Avax\Container\DependencyInjection\Capabilities\Prototypes\Analyze\ReflectionTypeAnalyzer;
use Avax\Container\DependencyInjection\Capabilities\Prototypes\Cache\PrototypeCache;
use Avax\Container\DependencyInjection\Capabilities\Prototypes\Factory\ServicePrototypeFactory;
use Avax\Container\DependencyInjection\Capabilities\Prototypes\Model\ServicePrototype;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

final class ServicePrototypeFactoryTest extends TestCase
{
    private PrototypeCache $cache;

    private ServicePrototypeFactory $factory;

    public function test_builds_prototype_for_simple_class() : void
    {
        $this->cache->expects(invocationRule: $this->once())
            ->method(constraint: 'get')
            ->willReturn(value: null);

        $this->cache->expects(invocationRule: $this->once())
            ->method(constraint: 'set');

        $prototype = $this->factory->createFor(class: stdClass::class);

        $this->assertInstanceOf(expected: ServicePrototype::class, actual: $prototype);
        $this->assertSame(expected: stdClass::class, actual: $prototype->class);
        $this->assertTrue(condition: $prototype->isInstantiable);
    }

    public function test_returns_cached_prototype() : void
    {
        $cachedPrototype = new ServicePrototype(
            class             : stdClass::class,
            constructor       : null,
            injectedProperties: [],
            injectedMethods   : [],
            isInstantiable    : true
        );

        $this->cache->expects(invocationRule: $this->once())
            ->method(constraint: 'get')
            ->willReturn(value: $cachedPrototype);

        $this->cache->expects(invocationRule: $this->never())
            ->method(constraint: 'set');

        $result = $this->factory->createFor(class: stdClass::class);

        $this->assertSame(expected: $cachedPrototype, actual: $result);
    }

    public function test_throws_exception_for_non_instantiable_class() : void
    {
        $this->expectException(exception: RuntimeException::class);
        $this->expectExceptionMessage(message: 'Cannot create prototype for non-instantiable class');

        $this->factory->createFor(class: 'Iterator');
    }

    protected function setUp() : void
    {
        $this->cache   = $this->createMock(PrototypeCache::class);
        $this->factory = new ServicePrototypeFactory(
            cache   : $this->cache,
            analyzer: new PrototypeAnalyzer(typeAnalyzer: new ReflectionTypeAnalyzer)
        );
    }
}

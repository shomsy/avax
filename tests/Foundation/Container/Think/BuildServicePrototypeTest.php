<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Think;

use Avax\Container\Features\Think\Analyze\PrototypeAnalyzer;
use Avax\Container\Features\Think\Analyze\ReflectionTypeAnalyzer;
use Avax\Container\Features\Think\Cache\PrototypeCache;
use Avax\Container\Features\Think\Model\ServicePrototype;
use Avax\Container\Features\Think\Prototype\ServicePrototypeFactory;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

/**
 * PHPUnit test coverage for Container component behavior.
 *
 * @see docs_md/tests/Think/BuildServicePrototypeTest.md#quick-summary
 */
class BuildServicePrototypeTest extends TestCase
{
    private PrototypeCache $cache;

    private ReflectionTypeAnalyzer $typeAnalyzer;

    private ServicePrototypeFactory $factory;

    /**
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     */
    public function test_builds_prototype_for_simple_class() : void
    {
        $this->cache->expects(invocationRule: $this->once())
            ->method(constraint: 'get')
            ->willReturn(value: null);

        $this->cache->expects(invocationRule: $this->once())
            ->method(constraint: 'set');

        $prototype = $this->factory->createFor(class: stdClass::class);

        $this->assertInstanceOf(expected: ServicePrototype::class, actual: $prototype);
        $this->assertEquals(expected: stdClass::class, actual: $prototype->class);
        $this->assertTrue(condition: $prototype->isInstantiable);
    }

    /**
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     */
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

    /**
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     */
    public function test_throws_exception_for_non_instantiable_class() : void
    {
        $this->expectException(exception: RuntimeException::class);
        $this->expectExceptionMessage(message: 'Cannot create prototype for non-instantiable class');

        $this->factory->createFor(class: 'Iterator'); // Interface
    }

    protected function setUp() : void
    {
        $this->cache        = $this->createMock(PrototypeCache::class);
        $this->typeAnalyzer = new ReflectionTypeAnalyzer;
        $this->factory      = new ServicePrototypeFactory(
            cache   : $this->cache,
            analyzer: new PrototypeAnalyzer(typeAnalyzer: $this->typeAnalyzer)
        );
    }
}

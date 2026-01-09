<?php

declare(strict_types=1);
namespace Avax\Container\tests\Think;

use Avax\Container\Features\Think\Analyze\ReflectionTypeAnalyzer;
use Avax\Container\Features\Think\Cache\PrototypeCache;
use Avax\Container\Features\Think\Model\ServicePrototype;
use Avax\Container\Features\Think\Prototype\DependencyInjectionPrototypeFactory;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

/**
 * Test suite for PrototypeFactory build behavior.
 *
 * @covers \Avax\Container\Features\Think\Prototype\DependencyInjectionPrototypeFactory
 */
class BuildServicePrototypeTest extends TestCase
{
    private PrototypeCache                      $cache;
    private ReflectionTypeAnalyzer              $typeAnalyzer;
    private DependencyInjectionPrototypeFactory $factory;

    /**
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     */
    public function testBuildsPrototypeForSimpleClass() : void
    {
        $this->cache->expects(invocationRule: $this->once())
            ->method(constraint: 'get')
            ->willReturn(value: null);

        $this->cache->expects(invocationRule: $this->once())
            ->method(constraint: 'set');

        $prototype = $this->factory->analyzeReflectionFor(class: stdClass::class);

        $this->assertInstanceOf(expected: ServicePrototype::class, actual: $prototype);
        $this->assertEquals(expected: stdClass::class, actual: $prototype->class);
        $this->assertTrue(condition: $prototype->isInstantiable);
    }

    /**
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     */
    public function testReturnsCachedPrototype() : void
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

        $result = $this->factory->analyzeReflectionFor(class: stdClass::class);

        $this->assertSame(expected: $cachedPrototype, actual: $result);
    }

    /**
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     */
    public function testThrowsExceptionForNonInstantiableClass() : void
    {
        $this->expectException(exception: RuntimeException::class);
        $this->expectExceptionMessage(message: 'Cannot create prototype for non-instantiable class');

        $this->factory->analyzeReflectionFor(class: 'Iterator'); // Interface
    }

    protected function setUp() : void
    {
        $this->cache        = $this->createMock(PrototypeCache::class);
        $this->typeAnalyzer = new ReflectionTypeAnalyzer();
        $this->factory      = new DependencyInjectionPrototypeFactory(
            cache                 : $this->cache,
            reflectionTypeAnalyzer: $this->typeAnalyzer
        );
    }
}
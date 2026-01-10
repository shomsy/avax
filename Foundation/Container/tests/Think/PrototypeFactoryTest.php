<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Think;

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
 * @see docs_md/tests/Think/PrototypeFactoryTest.md#quick-summary
 */
class PrototypeFactoryTest extends TestCase
{
    private PrototypeCache          $cache;
    private ReflectionTypeAnalyzer  $typeAnalyzer;
    private ServicePrototypeFactory $factory;

    /**
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     */
    public function testBuildsPrototypeForSimpleClass(): void
    {
        $this->cache->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $this->cache->expects($this->once())
            ->method('set');

        $prototype = $this->factory->createFor(class: stdClass::class);

        $this->assertInstanceOf(expected: ServicePrototype::class, actual: $prototype);
        $this->assertEquals(expected: stdClass::class, actual: $prototype->class);
        $this->assertTrue(condition: $prototype->isInstantiable);
    }

    /**
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     */
    public function testReturnsCachedPrototype(): void
    {
        $cachedPrototype = new ServicePrototype(
            class: stdClass::class,
            constructor: null,
            injectedProperties: [],
            injectedMethods: [],
            isInstantiable: true
        );

        $this->cache->expects($this->once())
            ->method('get')
            ->willReturn($cachedPrototype);

        $this->cache->expects($this->never())
            ->method('set');

        $result = $this->factory->createFor(class: stdClass::class);

        $this->assertSame(expected: $cachedPrototype, actual: $result);
    }

    /**
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     */
    public function testThrowsExceptionForNonInstantiableClass(): void
    {
        $this->expectException(exception: RuntimeException::class);
        $this->expectExceptionMessage(message: 'Cannot create prototype for non-instantiable class');

        $this->factory->createFor(class: 'Iterator'); // Interface
    }

    protected function setUp(): void
    {
        $this->cache        = $this->createMock(PrototypeCache::class);
        $this->typeAnalyzer = new ReflectionTypeAnalyzer();
        $this->factory      = new ServicePrototypeFactory(
            cache: $this->cache,
            analyzer: new \Avax\Container\Features\Think\Analyze\PrototypeAnalyzer(typeAnalyzer: $this->typeAnalyzer)
        );
    }
}

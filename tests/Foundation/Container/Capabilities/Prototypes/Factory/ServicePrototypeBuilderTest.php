<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Capability\Prototypes\Factory;

use Avax\Container\DependencyInjection\Capability\Prototypes\Factory\ServicePrototypeBuilder;
use Avax\Container\DependencyInjection\Capability\Prototypes\Model\MethodPrototype;
use Avax\Container\DependencyInjection\Capability\Prototypes\Model\ParameterPrototype;
use Avax\Container\DependencyInjection\Capability\Prototypes\Model\PropertyPrototype;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ServicePrototypeBuilderTest extends TestCase
{
    public function test_build_requires_target_class() : void
    {
        $this->expectException(exception: InvalidArgumentException::class);

        (new ServicePrototypeBuilder)->build();
    }

    public function test_build_creates_prototype_with_declared_parts() : void
    {
        $prototype = (new ServicePrototypeBuilder)
            ->for(class: BuilderFixture::class)
            ->withConstructor(prototype: new MethodPrototype(
                                             name      : '__construct',
                                             parameters: [new ParameterPrototype(name: 'logger', type: 'Psr\\Log\\LoggerInterface')]
                                         ))
            ->addProperty(new PropertyPrototype(name: 'cache', type: 'Psr\\SimpleCache\\CacheInterface'))
            ->addMethod(new MethodPrototype(name: 'boot'))
            ->build();

        $this->assertSame(expected: BuilderFixture::class, actual: $prototype->class);
        $this->assertNotNull(actual: $prototype->constructor);
        $this->assertCount(expectedCount: 1, haystack: $prototype->injectedProperties);
        $this->assertCount(expectedCount: 1, haystack: $prototype->injectedMethods);
        $this->assertTrue(condition: $prototype->isInstantiable);
    }
}

final class BuilderFixture {}

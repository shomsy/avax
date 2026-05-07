<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\Dispatcher;

use Avax\Components\HTTP\Dispatcher\System\Capabilities\ArgumentResolution\ArgumentResolver;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionMethod;

final class DispatcherCapabilitiesTest extends TestCase
{
    public function test_argument_resolver_resolves_request_attribute() : void
    {
        $container = $this->createMock(ContainerInterface::class);
        $request   = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->with('name')->willReturn('John');

        $resolver = new ArgumentResolver($container);

        $object     = new class {
            public function action(string $name) {}
        };
        $reflection = new ReflectionMethod($object, 'action');

        $resolved = $resolver->resolve($reflection, $request);

        $this->assertSame(['John'], $resolved);
    }
}

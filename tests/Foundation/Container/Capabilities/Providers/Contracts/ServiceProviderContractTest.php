<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Capabilities\Providers\Contracts;

use Avax\Container\ContainerInterface;
use Avax\Container\DependencyInjection\Capabilities\Providers\Contracts\ServiceProviderInterface;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class ServiceProviderContractTest extends TestCase
{
    public function test_provider_contract_depends_on_public_container_interface() : void
    {
        $constructor = new ReflectionMethod(ServiceProviderInterface::class, '__construct');
        $parameter   = $constructor->getParameters()[0];

        $this->assertSame(
            expected: ContainerInterface::class,
            actual  : $parameter->getType()?->getName() ?? ''
        );
    }
}

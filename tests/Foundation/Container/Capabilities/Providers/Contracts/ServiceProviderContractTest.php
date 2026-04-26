<?php

declare(strict_types=1);

namespace components\Container\Tests\Capability\Providers\Contracts;

use components\Container\ContainerInterface;
use components\Container\DependencyInjection\Capability\Providers\Contracts\ServiceProviderInterface;
use components\Tests\TestCase;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class ServiceProviderContractTest extends TestCase
{
    public function test_provider_contract_depends_on_public_container_interface() : void
    {
        $constructor = new ReflectionMethod(objectOrMethod: ServiceProviderInterface::class, method: '__construct');
        $parameter   = $constructor->getParameters()[0];

        $this->assertSame(
            expected: ContainerInterface::class,
            actual  : $parameter->getType()?->getName() ?? ''
        );
    }
}

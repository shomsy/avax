<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Capability\Providers\Contracts;

use Avax\Container\ContainerInterface;
use Avax\Container\DependencyInjection\Capability\Providers\Contracts\ServiceProviderInterface;
use Avax\Tests\TestCase;
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

<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Container\Capabilities\Providers\Contracts;

use Avax\Tests\TestCase;
use components\Container\ContainerInterface;
use components\Container\DependencyInjection\Capability\Providers\Contracts\RegisterDependency;
use ReflectionMethod;

final class BaseRegisterDependencyContractTest extends TestCase
{
    public function test_provider_contract_depends_on_public_container_interface() : void
    {
        $constructor = new ReflectionMethod(objectOrMethod: RegisterDependency::class, method: '__construct');
        $parameter   = $constructor->getParameters()[0];

        $this->assertSame(
            expected: ContainerInterface::class,
            actual  : $parameter->getType()?->getName() ?? ''
        );
    }
}

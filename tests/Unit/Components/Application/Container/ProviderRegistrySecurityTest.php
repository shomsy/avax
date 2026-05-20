<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Container;

use Avax\Components\Application\Container\System\Capabilities\Providers\BaseRegisterDependency;
use Avax\Components\Application\Container\System\Capabilities\Providers\ProviderRegistry;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use PHPUnit\Framework\TestCase;

final class ProviderRegistrySecurityTest extends TestCase
{
    private ProviderRegistry $registry;
    private ContainerInterface $container;

    public function test_rejects_non_existent_provider_class(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('does not exist');

        $this->registry->register('NonExistentProvider');
    }

    public function test_rejects_provider_not_extending_base_register_dependency(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('does not extend BaseRegisterDependency');

        $this->registry->register(NotAProvider::class);
    }

    public function test_accepts_valid_provider_implementing_base_register_dependency(): void
    {
        $this->registry->register(ValidTestProvider::class);

        $this->expectNotToPerformAssertions();
    }

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->registry = new ProviderRegistry($this->container);
    }
}

final class NotAProvider
{
    public function register(): void {}
}

final class ValidTestProvider extends BaseRegisterDependency
{
    public function register(): void {}
}

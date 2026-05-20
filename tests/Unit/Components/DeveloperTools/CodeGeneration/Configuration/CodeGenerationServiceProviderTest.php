<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DeveloperTools\CodeGeneration\Configuration;

use Avax\Components\DeveloperTools\CodeGeneration\System\Configuration\CodeGenerationServiceProvider;
use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use PHPUnit\Framework\TestCase;

final class CodeGenerationServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private CodeGenerationServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();
        $this->provider = new CodeGenerationServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_provider_registers_without_error(): void
    {
        // CodeGenerationServiceProvider delegates to Builders\RegisterCodeGenerationDefaults.
        // This test verifies the provider can be instantiated, registered, and booted without error.
        $this->assertInstanceOf(CodeGenerationServiceProvider::class, $this->provider);
    }
}

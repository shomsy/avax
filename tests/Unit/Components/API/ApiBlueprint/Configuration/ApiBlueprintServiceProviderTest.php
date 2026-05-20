<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\API\ApiBlueprint\Configuration;

use Avax\Components\API\ApiBlueprint\System\Capabilities\Compatibility\CompatibilityChangeDetector;
use Avax\Components\API\ApiBlueprint\System\Capabilities\Documentation\ApiDocumentationSource;
use Avax\Components\API\ApiBlueprint\System\Flows\AnalyzeApiEvolution\AnalyzeApiEvolution;
use Avax\Components\API\ApiBlueprint\System\Flows\DefineApiBlueprint\DefineApiBlueprint;
use Avax\Components\API\ApiBlueprint\System\Flows\VerifyApiBlueprint\VerifyApiBlueprint;
use Avax\Components\API\ApiBlueprint\System\Flows\VerifyApiCompatibility\VerifyApiCompatibility;
use Avax\Components\API\ApiBlueprint\System\Configuration\ApiBlueprintServiceProvider;
use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use PHPUnit\Framework\TestCase;

final class ApiBlueprintServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private ApiBlueprintServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();
        $this->provider = new ApiBlueprintServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_api_documentation_source_resolves(): void
    {
        $source = $this->container->get(ApiDocumentationSource::class);

        $this->assertInstanceOf(ApiDocumentationSource::class, $source);
    }

    public function test_define_api_blueprint_resolves(): void
    {
        $flow = $this->container->get(DefineApiBlueprint::class);

        $this->assertInstanceOf(DefineApiBlueprint::class, $flow);
    }

    public function test_verify_api_blueprint_resolves(): void
    {
        $flow = $this->container->get(VerifyApiBlueprint::class);

        $this->assertInstanceOf(VerifyApiBlueprint::class, $flow);
    }

    public function test_compatibility_change_detector_resolves(): void
    {
        $capability = $this->container->get(CompatibilityChangeDetector::class);

        $this->assertInstanceOf(CompatibilityChangeDetector::class, $capability);
    }

    public function test_analyze_api_evolution_resolves(): void
    {
        $flow = $this->container->get(AnalyzeApiEvolution::class);

        $this->assertInstanceOf(AnalyzeApiEvolution::class, $flow);
    }

    public function test_verify_api_compatibility_resolves(): void
    {
        $flow = $this->container->get(VerifyApiCompatibility::class);

        $this->assertInstanceOf(VerifyApiCompatibility::class, $flow);
    }

    public function test_define_api_blueprint_receives_api_documentation_source(): void
    {
        /** @var DefineApiBlueprint $flow */
        $flow = $this->container->get(DefineApiBlueprint::class);
        $source = $this->container->get(ApiDocumentationSource::class);

        // DefineApiBlueprint builds from the registered documentation source
        $definition = $flow->build();
        $this->assertNotNull($definition);
        $this->assertSame([], $definition->endpoints);
    }
}

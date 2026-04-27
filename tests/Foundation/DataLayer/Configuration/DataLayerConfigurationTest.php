<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\DataLayer\Configuration;

use Avax\DataLayer\ConfigureDataLayer\DataLayerConfig;
use Avax\DataLayer\ConfigureDataLayer\DataLayerConfigurationFailure;
use Avax\DataLayer\ConfigureDataLayer\RegisterDataLayerRuntime;
use Avax\DataLayer\ConfigureDataLayer\ResolveDataLayerRuntime;
use Avax\DataLayer\DataLayer;
use Avax\Tests\TestCase;
use stdClass;

final class DataLayerConfigurationTest extends TestCase
{
    public function testInvalidConfigFailsBeforeRuntimeIsUsed() : void
    {
        $this->expectException(DataLayerConfigurationFailure::class);

        new ResolveDataLayerRuntime()->resolve(config: new DataLayerConfig());
    }

    public function testDatabaseRuntimeIsRegisteredExplicitly() : void
    {
        $runtime = new stdClass();
        $config = new RegisterDataLayerRuntime()->register(databaseRuntime: $runtime);

        $resolved = new ResolveDataLayerRuntime()->resolve(config: $config);

        $this->assertSame($runtime, $resolved->databaseRuntime);
    }

    public function testDataLayerUsesRuntimeThroughAccessBoundary() : void
    {
        $runtime = new stdClass();

        $dataLayer = DataLayer::fromDatabaseRuntime(databaseRuntime: $runtime);

        $this->assertSame($runtime, $dataLayer->access()->databaseRuntime());
    }
}

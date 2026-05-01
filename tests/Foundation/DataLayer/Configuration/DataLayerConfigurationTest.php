<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\DataLayer\Configuration;

use Avax\Components\DataStack\Database\ConfigureDataLayer\DataLayerConfig;
use Avax\Components\DataStack\Database\ConfigureDataLayer\DataLayerConfigurationFailure;
use Avax\Components\DataStack\Database\ConfigureDataLayer\RegisterDataLayerRuntime;
use Avax\Components\DataStack\Database\ConfigureDataLayer\ResolveDataLayerRuntime;
use Avax\Components\DataStack\Database\DataLayer;
use Avax\Tests\TestCase;
use stdClass;

final class DataLayerConfigurationTest extends TestCase
{
    public function test_invalid_config_fails_before_runtime_is_used(): void
    {
        $this->expectException(DataLayerConfigurationFailure::class);

        new ResolveDataLayerRuntime()->resolve(config: new DataLayerConfig());
    }

    public function test_database_runtime_is_registered_explicitly(): void
    {
        $runtime = new stdClass();
        $config  = new RegisterDataLayerRuntime()->register(databaseRuntime: $runtime);

        $resolved = new ResolveDataLayerRuntime()->resolve(config: $config);

        $this->assertSame($runtime, $resolved->databaseRuntime);
    }

    public function test_data_layer_uses_runtime_through_access_boundary(): void
    {
        $runtime = new stdClass();

        $dataLayer = DataLayer::fromDatabaseRuntime(databaseRuntime: $runtime);

        $this->assertSame($runtime, $dataLayer->access()->databaseRuntime());
    }
}

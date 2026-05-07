<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\System;

use Avax\Components\DataStack\System\Capabilities\DataLayer\AccessPersistentData\AccessPersistentData;
use Avax\Components\DataStack\System\Capabilities\DataLayer\CommitDataChanges\CommitDataChanges;
use Avax\Components\DataStack\System\Capabilities\DataLayer\ConfigureDataLayer\DataLayerConfig;
use Avax\Components\DataStack\System\Capabilities\DataLayer\DataLayer;
use PHPUnit\Framework\TestCase;
use stdClass;

final class DataStackCapabilitiesTest extends TestCase
{
    public function test_data_layer_instantiation() : void
    {
        $databaseRuntime = new stdClass();
        $dataLayer       = DataLayer::fromDatabaseRuntime($databaseRuntime);

        $this->assertInstanceOf(DataLayer::class, $dataLayer);
        $this->assertInstanceOf(AccessPersistentData::class, $dataLayer->access());
        $this->assertInstanceOf(CommitDataChanges::class, $dataLayer->commit());
        $this->assertInstanceOf(DataLayerConfig::class, $dataLayer->configuration());
    }
}

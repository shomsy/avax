<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\System;

use Avax\Components\DataStack\System\System\Capabilities\DataLayer\DataLayer;
use PHPUnit\Framework\TestCase;
use stdClass;

final class DataStackCapabilitiesTest extends TestCase
{
    public function test_data_layer_instantiation() : void
    {
        $databaseRuntime = new stdClass();
        $dataLayer       = DataLayer::fromDatabaseRuntime($databaseRuntime);

        $this->assertInstanceOf(DataLayer::class, $dataLayer);
        $this->assertNotNull($dataLayer->access());
        $this->assertNotNull($dataLayer->commit());
        $this->assertNotNull($dataLayer->configuration());
    }
}

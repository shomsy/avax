<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Data\Configuration;

use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use Avax\Components\DataStack\Data\System\Capabilities\Operators\Arrays\ArrayReader;
use Avax\Components\DataStack\Data\System\Capabilities\Operators\Arrays\ArrayWriter;
use Avax\Components\DataStack\Data\System\Configuration\DataServiceProvider;
use Avax\Components\DataStack\Data\System\Flows\Aggregate\AverageValues;
use Avax\Components\DataStack\Data\System\Flows\Aggregate\SumValues;
use Avax\Components\DataStack\Data\System\Flows\Read\ReadNestedValue;
use Avax\Components\DataStack\Data\System\Flows\Write\WriteNestedValue;
use Avax\Components\DataStack\Data\System\PublicSurface\Data;
use Avax\Components\DataStack\Data\System\PublicSurface\DataInterface;
use PHPUnit\Framework\TestCase;

final class DataServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private DataServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();
        $this->provider = new DataServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_array_reader_resolves(): void
    {
        $reader = $this->container->get(ArrayReader::class);

        $this->assertInstanceOf(ArrayReader::class, $reader);
    }

    public function test_array_writer_resolves(): void
    {
        $writer = $this->container->get(ArrayWriter::class);

        $this->assertInstanceOf(ArrayWriter::class, $writer);
    }

    public function test_read_nested_value_resolves(): void
    {
        $flow = $this->container->get(ReadNestedValue::class);

        $this->assertInstanceOf(ReadNestedValue::class, $flow);
    }

    public function test_write_nested_value_resolves(): void
    {
        $flow = $this->container->get(WriteNestedValue::class);

        $this->assertInstanceOf(WriteNestedValue::class, $flow);
    }

    public function test_sum_values_resolves(): void
    {
        $flow = $this->container->get(SumValues::class);

        $this->assertInstanceOf(SumValues::class, $flow);
    }

    public function test_average_values_resolves(): void
    {
        $flow = $this->container->get(AverageValues::class);

        $this->assertInstanceOf(AverageValues::class, $flow);
    }

    public function test_data_resolves(): void
    {
        $data = $this->container->get(Data::class);

        $this->assertInstanceOf(Data::class, $data);
    }

    public function test_data_interface_resolves(): void
    {
        $data = $this->container->get(DataInterface::class);

        $this->assertInstanceOf(DataInterface::class, $data);
        $this->assertInstanceOf(Data::class, $data);
    }
}

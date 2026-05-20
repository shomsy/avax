<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\DataStack\Data\System\Capabilities\Operators\Arrays\ArrayReader;
use Avax\Components\DataStack\Data\System\Capabilities\Operators\Arrays\ArrayWriter;
use Avax\Components\DataStack\Data\System\Flows\Aggregate\AverageValues;
use Avax\Components\DataStack\Data\System\Flows\Aggregate\SumValues;
use Avax\Components\DataStack\Data\System\Flows\Read\ReadNestedValue;
use Avax\Components\DataStack\Data\System\Flows\Write\WriteNestedValue;
use Avax\Components\DataStack\Data\System\PublicSurface\Data;
use Avax\Components\DataStack\Data\System\PublicSurface\DataInterface;

/**
 * DataServiceProvider — registers DataStack/Data component dependencies.
 *
 * Registers array operators, data flows, and the Data PublicSurface facade.
 */
final class DataServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // ArrayReader — type-safe, immutable array reading with no external dependencies
        $container->singleton(ArrayReader::class, static fn () : ArrayReader => new ArrayReader());

        // ArrayWriter — type-safe array writing with no external dependencies
        $container->singleton(ArrayWriter::class, static fn () : ArrayWriter => new ArrayWriter());

        // ReadNestedValue — Flow to read nested values via dot-notation with no external dependencies
        $container->singleton(ReadNestedValue::class, static fn () : ReadNestedValue => new ReadNestedValue());

        // WriteNestedValue — Flow to write nested values via dot-notation with no external dependencies
        $container->singleton(WriteNestedValue::class, static fn () : WriteNestedValue => new WriteNestedValue());

        // SumValues — Flow to sum values in a collection with no external dependencies
        $container->singleton(SumValues::class, static fn () : SumValues => new SumValues());

        // AverageValues — Flow to average values, depends on SumValues
        $container->singleton(AverageValues::class, static fn (ContainerInterface $c) : AverageValues => new AverageValues(
            sumValues: $c->get(SumValues::class),
        ));

        // Data — PublicSurface facade, needs all operators and flows
        $container->singleton(Data::class, static fn (ContainerInterface $c) : Data => new Data(
            arrayReader     : $c->get(ArrayReader::class),
            arrayWriter     : $c->get(ArrayWriter::class),
            readNestedValue : $c->get(ReadNestedValue::class),
            writeNestedValue: $c->get(WriteNestedValue::class),
            sumValues       : $c->get(SumValues::class),
            averageValues   : $c->get(AverageValues::class),
        ));

        // DataInterface -> Data
        $container->singleton(DataInterface::class, static fn (ContainerInterface $c) : DataInterface => $c->get(Data::class));
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}

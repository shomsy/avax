<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database\Migrations;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema\SchemaBuilder;
use PHPUnit\Framework\TestCase;

final class SchemaBuilderTest extends TestCase
{
    public function test_schema_builder_has_create_method(): void
    {
        $builder = new SchemaBuilder();

        $this->assertTrue(method_exists($builder, 'create'));
    }

    public function test_schema_builder_has_drop_method(): void
    {
        $builder = new SchemaBuilder();

        $this->assertTrue(method_exists($builder, 'drop'));
    }

    public function test_schema_builder_is_instantiable(): void
    {
        $builder = new SchemaBuilder();

        $this->assertInstanceOf(SchemaBuilder::class, $builder);
    }
}

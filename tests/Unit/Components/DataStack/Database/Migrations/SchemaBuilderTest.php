<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database\Migrations;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema\SchemaBuilder;
use PHPUnit\Framework\TestCase;

final class SchemaBuilderTest extends TestCase
{
    public function testSchemaBuilderHasCreateMethod() : void
    {
        $builder = new SchemaBuilder();

        $this->assertTrue(method_exists($builder, 'create'));
    }

    public function testSchemaBuilderHasDropMethod() : void
    {
        $builder = new SchemaBuilder();

        $this->assertTrue(method_exists($builder, 'drop'));
    }

    public function testSchemaBuilderIsInstantiable() : void
    {
        $builder = new SchemaBuilder();

        $this->assertInstanceOf(SchemaBuilder::class, $builder);
    }
}
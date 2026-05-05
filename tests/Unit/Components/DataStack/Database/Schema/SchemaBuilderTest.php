<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database\Schema;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema\SchemaBuilder;
use PHPUnit\Framework\TestCase;

final class SchemaBuilderTest extends TestCase
{
    public function testSchemaBuilderCanCreateTable() : void
    {
        $builder = new SchemaBuilder();

        $this->assertTrue(method_exists($builder, 'create'));
    }

    public function testSchemaBuilderCanDropTable() : void
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
<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database\Migrations;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Design\Table\Blueprint;
use PHPUnit\Framework\TestCase;

final class BlueprintTest extends TestCase
{
    public function test_blueprint_can_be_created_with_table_name(): void
    {
        $blueprint = new Blueprint(table: 'users');

        $this->assertInstanceOf(Blueprint::class, $blueprint);
    }

    public function test_blueprint_has_id_column_method(): void
    {
        $blueprint = new Blueprint(table: 'users');

        $this->assertTrue(method_exists($blueprint, 'id'));
    }

    public function test_blueprint_has_string_column_method(): void
    {
        $blueprint = new Blueprint(table: 'users');

        $this->assertTrue(method_exists($blueprint, 'string'));
    }

    public function test_blueprint_has_integer_column_method(): void
    {
        $blueprint = new Blueprint(table: 'users');

        $this->assertTrue(method_exists($blueprint, 'integer'));
    }

    public function test_blueprint_has_boolean_column_method(): void
    {
        $blueprint = new Blueprint(table: 'users');

        $this->assertTrue(method_exists($blueprint, 'boolean'));
    }

    public function test_blueprint_has_timestamp_column_method(): void
    {
        $blueprint = new Blueprint(table: 'users');

        $this->assertTrue(method_exists($blueprint, 'timestamp'));
    }

    public function test_blueprint_has_text_column_method(): void
    {
        $blueprint = new Blueprint(table: 'users');

        $this->assertTrue(method_exists($blueprint, 'text'));
    }

    public function test_blueprint_has_set_alter_mode(): void
    {
        $blueprint = new Blueprint(table: 'users');

        $this->assertTrue(method_exists($blueprint, 'setAlterMode'));
    }

    public function test_blueprint_set_alter_mode_returns_self(): void
    {
        $blueprint = new Blueprint(table: 'users');
        $result = $blueprint->setAlterMode();

        $this->assertSame($blueprint, $result);
    }

    public function test_blueprint_to_sql_method_exists(): void
    {
        $blueprint = new Blueprint(table: 'users');

        $this->assertTrue(method_exists($blueprint, 'toSql'));
    }
}

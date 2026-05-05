<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database\Migrations;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Design\Table\Blueprint;
use PHPUnit\Framework\TestCase;

final class BlueprintTest extends TestCase
{
    public function testBlueprintCanBeCreatedWithTableName() : void
    {
        $blueprint = new Blueprint(table: 'users');

        $this->assertInstanceOf(Blueprint::class, $blueprint);
    }

    public function testBlueprintHasIdColumnMethod() : void
    {
        $blueprint = new Blueprint(table: 'users');

        $this->assertTrue(method_exists($blueprint, 'id'));
    }

    public function testBlueprintHasStringColumnMethod() : void
    {
        $blueprint = new Blueprint(table: 'users');

        $this->assertTrue(method_exists($blueprint, 'string'));
    }

    public function testBlueprintHasIntegerColumnMethod() : void
    {
        $blueprint = new Blueprint(table: 'users');

        $this->assertTrue(method_exists($blueprint, 'integer'));
    }

    public function testBlueprintHasBooleanColumnMethod() : void
    {
        $blueprint = new Blueprint(table: 'users');

        $this->assertTrue(method_exists($blueprint, 'boolean'));
    }

    public function testBlueprintHasTimestampColumnMethod() : void
    {
        $blueprint = new Blueprint(table: 'users');

        $this->assertTrue(method_exists($blueprint, 'timestamp'));
    }

    public function testBlueprintHasTextColumnMethod() : void
    {
        $blueprint = new Blueprint(table: 'users');

        $this->assertTrue(method_exists($blueprint, 'text'));
    }

    public function testBlueprintHasSetAlterMode() : void
    {
        $blueprint = new Blueprint(table: 'users');

        $this->assertTrue(method_exists($blueprint, 'setAlterMode'));
    }

    public function testBlueprintSetAlterModeReturnsSelf() : void
    {
        $blueprint = new Blueprint(table: 'users');
        $result    = $blueprint->setAlterMode();

        $this->assertSame($blueprint, $result);
    }

    public function testBlueprintToSqlMethodExists() : void
    {
        $blueprint = new Blueprint(table: 'users');

        $this->assertTrue(method_exists($blueprint, 'toSql'));
    }
}
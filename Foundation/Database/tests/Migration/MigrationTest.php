<?php

declare(strict_types=1);

namespace Avax\Tests\Migration;

use Avax\Database\Modules\Migration\Blueprint;
use Avax\Database\Modules\Migration\TableRenderer;
use Avax\Tests\TestCase;

class MigrationTest extends TestCase
{
    public function testBlueprintGeneratesColumns() : void
    {
        $blueprint = new Blueprint('users');
        $blueprint->id();
        $blueprint->string('email');
        $blueprint->timestamps();

        $this->assertCount(4, $blueprint->getColumns());
    }

    public function testTableRendererGeneratesSQL() : void
    {
        $blueprint = new Blueprint('users');
        $blueprint->id();
        $blueprint->string('name');

        $sql = TableRenderer::renderCreate($blueprint);

        $this->assertStringContainsString('CREATE TABLE `users`', $sql);
        $this->assertStringContainsString('`id`', $sql);
        $this->assertStringContainsString('`name`', $sql);
    }
}

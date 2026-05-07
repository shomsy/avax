<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\CLI\System;

use Avax\Components\CLI\System\System\PublicSurface\Cli;
use PHPUnit\Framework\TestCase;

final class CliSystemTest extends TestCase
{
    public function test_it_can_create_cli_instance() : void
    {
        $cli = new Cli();
        $this->assertInstanceOf(Cli::class, $cli);
    }
}

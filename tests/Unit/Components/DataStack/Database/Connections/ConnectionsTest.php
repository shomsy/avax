<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database\Connections;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Connections;
use PHPUnit\Framework\TestCase;

final class ConnectionsTest extends TestCase
{
    public function testConnectionsClassExists() : void
    {
        $this->assertTrue(class_exists(Connections::class));
    }

    public function testConnectionsHasConnectionMethod() : void
    {
        $this->assertTrue(method_exists(Connections::class, 'connection'));
    }

    public function testConnectionsHasPoolMethod() : void
    {
        $this->assertTrue(method_exists(Connections::class, 'pool'));
    }

    public function testConnectionsHasPdoMethod() : void
    {
        $this->assertTrue(method_exists(Connections::class, 'pdo'));
    }

    public function testConnectionsHasWithScopeMethod() : void
    {
        $this->assertTrue(method_exists(Connections::class, 'withScope'));
    }
}
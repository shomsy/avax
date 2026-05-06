<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database\Connections;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Connections;
use PHPUnit\Framework\TestCase;

final class ConnectionsTest extends TestCase
{
    public function test_connections_class_exists(): void
    {
        $this->assertTrue(class_exists(Connections::class));
    }

    public function test_connections_has_connection_method(): void
    {
        $this->assertTrue(method_exists(Connections::class, 'connection'));
    }

    public function test_connections_has_pool_method(): void
    {
        $this->assertTrue(method_exists(Connections::class, 'pool'));
    }

    public function test_connections_has_pdo_method(): void
    {
        $this->assertTrue(method_exists(Connections::class, 'pdo'));
    }

    public function test_connections_has_with_scope_method(): void
    {
        $this->assertTrue(method_exists(Connections::class, 'withScope'));
    }
}

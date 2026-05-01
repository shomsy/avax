<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Session\Capabilities;

use Avax\Tests\TestCase;

final class SessionStoreTest extends TestCase
{
    public function test_store_placeholder() : void
    {
        // Characterization placeholder for store implementation behavior (Array, Native, File, Redis).
        $this->assertTrue(true);
    }
}

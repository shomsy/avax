<?php

namespace Avax\Tests\Foundation\HTTP\Session\PublicSurface;

use Avax\HTTP\Session\Session;
use Avax\Tests\TestCase;

final class SessionTest extends TestCase
{
    public function test_public_surface_class_exists() : void
    {
        $this->assertTrue(class_exists(\Avax\HTTP\Session\Session::class));
    }
}

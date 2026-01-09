<?php

declare(strict_types=1);

namespace Avax\Tests;

use Avax\Database\Kernel;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected Kernel $kernel;

    protected function setUp() : void
    {
        parent::setUp();

        $this->kernel = Kernel::getInstance();
        $this->kernel->bootstrap([
            'database' => [
                'default'     => 'sqlite',
                'connections' => [
                    'sqlite' => [
                        'driver'   => 'sqlite',
                        'database' => ':memory:',
                        'prefix'   => '',
                    ]
                ]
            ]
        ]);
    }

    protected function tearDown() : void
    {
        $this->kernel->shutdown();
        parent::tearDown();
    }
}

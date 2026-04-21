<?php

declare(strict_types=1);

namespace Avax\Tests;

use Avax\Database\Kernel;
use Override;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected Kernel $kernel;

    #[Override]
    protected function setUp() : void
    {
        parent::setUp();

        if (! class_exists(Kernel::class)) {
            $this->markTestSkipped(message: 'Database kernel is not available in this installation.');
        }

        $this->kernel = Kernel::getInstance();
        $this->kernel->bootstrap([
                                     'database' => [
                                         'default'     => 'sqlite',
                                         'connections' => [
                                             'sqlite' => [
                                                 'driver'   => 'sqlite',
                                                 'database' => ':memory:',
                                                 'prefix'   => '',
                                             ],
                                         ],
                                     ],
                                 ]);
    }

    #[Override]
    protected function tearDown() : void
    {
        $this->kernel->shutdown();
        parent::tearDown();
    }
}

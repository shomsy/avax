<?php

declare(strict_types=1);

namespace Avax\Tests;

use Avax\Database\Database;
use Override;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Random\RandomException;

abstract class TestCase extends BaseTestCase
{
    protected Database $database;

    #[Override]
    /**
     * @throws RandomException
     */
    protected function setUp() : void
    {
        parent::setUp();

        $this->database = Database::configuration()->usingConfig([
                                                                     'default'     => 'sqlite',
                                                                     'connections' => [
                                                                         'sqlite' => [
                                                                             'driver'   => 'sqlite',
                                                                             'database' => ':memory:',
                                                                             'prefix'   => '',
                                                                         ],
                                                                     ],
                                                                 ])->ready();
    }

    #[Override]
    protected function tearDown() : void
    {
        parent::tearDown();
    }
}

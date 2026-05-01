<?php

declare(strict_types=1);

namespace Avax\Tests\Framework;

use Override;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();
    }
}

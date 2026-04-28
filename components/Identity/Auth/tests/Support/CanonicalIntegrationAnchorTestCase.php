<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Support;

use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

abstract class CanonicalIntegrationAnchorTestCase extends TestCase
{
    final public function testCanonicalFlowAnchorTargetsExistingCoverage() : void
    {
        $canonicalTest = static::canonicalTestPath();

        self::assertNotSame(expected: '', actual: $canonicalTest);
        self::assertFileExists(filename: $canonicalTest);
    }

    abstract protected static function canonicalTestPath() : string;
}

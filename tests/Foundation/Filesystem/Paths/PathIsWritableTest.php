<?php

declare(strict_types=1);

namespace components\Tests\Foundation\Filesystem\Paths;

use components\Filesystem\Paths\PathIsWritable;
use components\Tests\TestCase;

class PathIsWritableTest extends TestCase
{
    private string $testDir;

    protected function setUp() : void
    {
        parent::setUp();
        $this->testDir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/writable_test';
        @mkdir(directory: $this->testDir, permissions: 0755, recursive: true);
    }

    protected function tearDown() : void
    {
        @rmdir(directory: $this->testDir);
        parent::tearDown();
    }

    public function testExecuteReturnsTrueForWritableDirectory() : void
    {
        $result = new PathIsWritable()->execute(path: $this->testDir);

        self::assertTrue(condition: $result);
    }

    public function testExecuteReturnsFalseForNonExistentPath() : void
    {
        $result = new PathIsWritable()->execute(path: '/ne postoji put');

        self::assertFalse(condition: $result);
    }

    public function testExecuteReturnsFalseForUnwritablePath() : void
    {
        if (posix_getuid() === 0) {
            $this->markTestSkipped(reason: 'Cannot test unwritable paths as root');
        }

        $result = new PathIsWritable()->execute(path: '/root');

        self::assertFalse(condition: $result);
    }
}
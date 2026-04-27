<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Directories;

use Avax\Filesystem\Directories\EnsureDirectoryIsWritable;
use Avax\Filesystem\Disks\Local\LocalDisk;
use Avax\Tests\TestCase;

final class EnsureDirectoryIsWritableTest extends TestCase
{
    private LocalDisk $disk;
    private string    $testDir;

    public function testExecuteCreatesDirectoryWhenMissing() : void
    {
        $result = new EnsureDirectoryIsWritable(disk: $this->disk)->execute(path: $this->testDir);

        self::assertTrue($result);
        self::assertTrue(is_dir(filename: $this->testDir));
        self::assertTrue(is_writable(filename: $this->testDir));
    }

    public function testExecuteRepairsPermissionsWhenDirectoryExists() : void
    {
        if (posix_getuid() === 0) {
            $this->markTestSkipped(reason: 'Cannot validate writability transitions as root');
        }

        mkdir(directory: $this->testDir, permissions: 0755, recursive: true);
        chmod(filename: $this->testDir, permissions: 0555);

        self::assertFalse(is_writable(filename: $this->testDir));
        self::assertTrue(new EnsureDirectoryIsWritable(disk: $this->disk)->execute(path: $this->testDir));
        self::assertTrue(is_writable(filename: $this->testDir));
    }

    protected function setUp() : void
    {
        parent::setUp();
        $this->disk    = new LocalDisk();
        $this->testDir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/ensure_writable_test';
    }

    protected function tearDown() : void
    {
        if (is_dir(filename: $this->testDir)) {
            $this->disk->deleteDirectory(path: $this->testDir);
        }

        parent::tearDown();
    }
}

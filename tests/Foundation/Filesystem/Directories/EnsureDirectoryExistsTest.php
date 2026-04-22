<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Directories;

use Avax\Filesystem\Directories\EnsureDirectoryExists;
use Avax\Filesystem\Disks\Local\LocalDisk;
use PHPUnit\Framework\TestCase;

class EnsureDirectoryExistsTest extends TestCase
{
    private LocalDisk $disk;
    private string $testDir;

    protected function setUp() : void
    {
        parent::setUp();
        $this->disk = new LocalDisk();
        $this->testDir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/ensure_dir_test';
    }

    protected function tearDown() : void
    {
        @rmdir(directory: $this->testDir);
        parent::tearDown();
    }

    public function testExecuteCreatesDirectoryIfNotExists() : void
    {
        $result = (new EnsureDirectoryExists(disk: $this->disk))->execute(path: $this->testDir);

        self::assertTrue(condition: $result);
        self::assertTrue(condition: is_dir(filename: $this->testDir));
    }

    public function testExecuteReturnsTrueIfExists() : void
    {
        mkdir(directory: $this->testDir, permissions: 0755, recursive: true);

        $result = (new EnsureDirectoryExists(disk: $this->disk))->execute(path: $this->testDir);

        self::assertTrue(condition: $result);
    }
}
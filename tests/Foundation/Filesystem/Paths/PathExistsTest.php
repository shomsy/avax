<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Paths;

use Avax\Filesystem\Paths\PathExists;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

class PathExistsTest extends TestCase
{
    private string $testFile;
    private string $testDir;

    protected function setUp() : void
    {
        parent::setUp();
        $this->testFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/path_exists_test.txt';
        $this->testDir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/path_exists_dir';
    }

    protected function tearDown() : void
    {
        @unlink(filename: $this->testFile);
        @rmdir(directory: $this->testDir);
        parent::tearDown();
    }

    public function testExecuteReturnsFalseForNonExistentPath() : void
    {
        $result = (new PathExists())->execute(path: '/ne postoji put');

        self::assertFalse(condition: $result);
    }

    public function testExecuteReturnsTrueForExistingFile() : void
    {
        file_put_contents(filename: $this->testFile, data: "sadrzaj\n");

        $result = (new PathExists())->execute(path: $this->testFile);

        self::assertTrue(condition: $result);
    }

    public function testExecuteReturnsTrueForExistingDirectory() : void
    {
        @mkdir(directory: $this->testDir, permissions: 0755, recursive: true);

        $result = (new PathExists())->execute(path: $this->testDir);

        self::assertTrue(condition: $result);
    }
}
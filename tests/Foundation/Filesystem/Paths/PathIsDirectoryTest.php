<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Paths;

use Avax\Tests\TestCase;
use Avax\Components\Filesystem\Paths\PathIsDirectory;

class PathIsDirectoryTest extends TestCase
{
    private string $testFile;

    private string $testDir;

    protected function setUp() : void
    {
        parent::setUp();
        $this->testFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/is_dir_test.txt';
        $this->testDir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/is_dir_test';
        @mkdir(directory: $this->testDir, permissions: 0o755, recursive: true);
    }

    protected function tearDown() : void
    {
        @unlink(filename: $this->testFile);
        @rmdir(directory: $this->testDir);
        parent::tearDown();
    }

    public function test_execute_returns_false_for_file() : void
    {
        file_put_contents(filename: $this->testFile, data: "sadrzaj\n");

        $result = new PathIsDirectory()->execute(path: $this->testFile);

        self::assertFalse(condition: $result);
    }

    public function test_execute_returns_true_for_directory() : void
    {
        $result = new PathIsDirectory()->execute(path: $this->testDir);

        self::assertTrue(condition: $result);
    }

    public function test_execute_returns_false_for_non_existent_path() : void
    {
        $result = new PathIsDirectory()->execute(path: '/ne postoji put');

        self::assertFalse(condition: $result);
    }
}

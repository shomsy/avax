<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Paths;

use Avax\Tests\TestCase;
use components\Filesystem\Paths\PathExists;

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

    public function test_execute_returns_false_for_non_existent_path() : void
    {
        $result = new PathExists()->execute(path: '/ne postoji put');

        self::assertFalse(condition: $result);
    }

    public function test_execute_returns_true_for_existing_file() : void
    {
        file_put_contents(filename: $this->testFile, data: "sadrzaj\n");

        $result = new PathExists()->execute(path: $this->testFile);

        self::assertTrue(condition: $result);
    }

    public function test_execute_returns_true_for_existing_directory() : void
    {
        @mkdir(directory: $this->testDir, permissions: 0o755, recursive: true);

        $result = new PathExists()->execute(path: $this->testDir);

        self::assertTrue(condition: $result);
    }
}

<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Files;

use Avax\Components\Application\Filesystem\System\Capabilities\Disks\Local\LocalDisk;
use Avax\Filesystem\Files\WriteFile;
use Avax\Tests\TestCase;

class WriteFileTest extends TestCase
{
    private LocalDisk $disk;

    private string $testFile;

    protected function setUp() : void
    {
        parent::setUp();
        $this->disk = new LocalDisk();
        $this->testFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/write_test.txt';
    }

    protected function tearDown() : void
    {
        @unlink(filename: $this->testFile);
        parent::tearDown();
    }

    public function test_execute_creates_file() : void
    {
        $result = new WriteFile(disk: $this->disk)->execute(path: $this->testFile, content: 'test');

        self::assertTrue(condition: $result);
        self::assertFileExists(filename: $this->testFile);
    }

    public function test_execute_creates_parent_directory() : void
    {
        $file = '/home/shomsy/projects/components/tests/fixtures/Filesystem/subdir/nested/test.txt';

        try {
            new WriteFile(disk: $this->disk)->execute(path: $file, content: 'test');
        } finally {
            @unlink(filename: $file);
            @rmdir(directory: '/home/shomsy/projects/components/tests/fixtures/Filesystem/subdir/nested');
            @rmdir(directory: '/home/shomsy/projects/components/tests/fixtures/Filesystem/subdir');
        }

        self::assertFileExists(filename: $file);
    }

    public function test_execute_adds_newline_automatically() : void
    {
        new WriteFile(disk: $this->disk)->execute(path: $this->testFile, content: 'test');

        $content = file_get_contents(filename: $this->testFile);
        self::assertStringEndsWith("\n", $content);
    }
}

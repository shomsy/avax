<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Files;

use Avax\Filesystem\Files\AppendToFile;
use Avax\Filesystem\Disks\Local\LocalDisk;
use PHPUnit\Framework\TestCase;

class AppendToFileTest extends TestCase
{
    private LocalDisk $disk;
    private string $testFile;

    protected function setUp() : void
    {
        parent::setUp();
        $this->disk = new LocalDisk();
        $this->testFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/append_test.txt';
    }

    protected function tearDown() : void
    {
        @unlink(filename: $this->testFile);
        parent::tearDown();
    }

    public function testExecuteAppendsContent() : void
    {
        file_put_contents(filename: $this->testFile, data: "line1\n");

        (new AppendToFile(disk: $this->disk))->execute(path: $this->testFile, content: 'line2');

        $content = file_get_contents(filename: $this->testFile);
        self::assertStringContainsString(haystack: $content, needdle: 'line1');
        self::assertStringContainsString(haystack: $content, needdle: 'line2');
    }

    public function testExecuteCreatesParentDirectory() : void
    {
        $file = '/home/shomsy/projects/components/tests/fixtures/Filesystem/append_dir/test.txt';

        try {
            (new AppendToFile(disk: $this->disk))->execute(path: $file, content: 'test');
        } finally {
            @unlink(filename: $file);
            @rmdir(directory: '/home/shomsy/projects/components/tests/fixtures/Filesystem/append_dir');
        }

        self::assertFileExists(filename: $file);
    }
}
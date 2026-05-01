<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem;

use Avax\Tests\TestCase;
use components\Filesystem\Disks\Local\LocalDisk;
use components\Filesystem\Filesystem;

final class FilesystemTest extends TestCase
{
    private LocalDisk $disk;

    private Filesystem $filesystem;

    private string $testDir;

    public function test_put_get_exists_and_delete_round_trip() : void
    {
        $path = $this->testDir . '/round_trip.txt';

        $this->filesystem->put(path: $path, content: 'hello');

        self::assertTrue($this->filesystem->exists(path: $path));
        self::assertSame("hello\n", $this->filesystem->get(path: $path));

        $this->filesystem->delete(path: $path);

        self::assertFalse($this->filesystem->exists(path: $path));
    }

    public function test_ensure_directory_is_writable_creates_missing_directory() : void
    {
        $path = $this->testDir . '/nested/writable';

        self::assertTrue($this->filesystem->ensureDirectoryIsWritable(path: $path));
        self::assertTrue(is_dir(filename: $path));
        self::assertTrue($this->filesystem->isWritable(path: $path));
    }

    public function test_copy_move_list_files_and_last_modified() : void
    {
        $source = $this->testDir . '/source.txt';
        $copy   = $this->testDir . '/copy.txt';
        $moved  = $this->testDir . '/moved.txt';

        $this->filesystem->put(path: $source, content: 'content');
        $this->filesystem->copy(source: $source, destination: $copy);
        $this->filesystem->move(source: $source, destination: $moved);

        $files = $this->filesystem->listFiles(path: $this->testDir);

        self::assertCount(2, $files);
        self::assertContains($copy, $files);
        self::assertContains($moved, $files);
        self::assertFalse($this->filesystem->exists(path: $source));
        self::assertIsInt($this->filesystem->lastModified(path: $moved));
    }

    protected function setUp() : void
    {
        parent::setUp();

        $this->disk    = new LocalDisk;
        $this->filesystem = new Filesystem(disk: $this->disk);
        $this->testDir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/facade_test';

        @mkdir(directory: $this->testDir, permissions: 0o755, recursive: true);
    }

    protected function tearDown() : void
    {
        if (is_dir(filename: $this->testDir)) {
            $this->disk->deleteDirectory(path: $this->testDir);
        }

        parent::tearDown();
    }
}

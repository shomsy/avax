<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Filesystem;

use Avax\Components\Operations\Filesystem\System\Capabilities\Drivers\Local;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

final class FilesystemCapabilitiesTest extends TestCase
{
    private string $tempDir;

    private Local $storage;

    public function test_it_writes_and_reads_file() : void
    {
        $this->storage->put('hello.txt', 'Hello World');
        $this->assertSame('Hello World', $this->storage->get('hello.txt'));
    }

    public function test_it_returns_null_when_file_does_not_exist() : void
    {
        $this->assertNull($this->storage->get('missing.txt'));
    }

    public function test_it_checks_file_existence() : void
    {
        $this->assertFalse($this->storage->exists('check.txt'));
        $this->storage->put('check.txt', 'data');
        $this->assertTrue($this->storage->exists('check.txt'));
    }

    public function test_it_deletes_file() : void
    {
        $this->storage->put('delete-me.txt', 'data');
        $this->assertTrue($this->storage->delete('delete-me.txt'));
        $this->assertFalse($this->storage->exists('delete-me.txt'));
    }

    public function test_it_returns_file_size() : void
    {
        $this->storage->put('sized.txt', '12345');
        $this->assertSame(5, $this->storage->size('sized.txt'));
    }

    public function test_it_copies_file() : void
    {
        $this->storage->put('src.txt', 'content');
        $this->storage->copy('src.txt', 'dest.txt');
        $this->assertSame('content', $this->storage->get('dest.txt'));
        $this->assertTrue($this->storage->exists('src.txt'));
    }

    public function test_it_moves_file() : void
    {
        $this->storage->put('move-src.txt', 'content');
        $this->storage->move('move-src.txt', 'move-dest.txt');
        $this->assertSame('content', $this->storage->get('move-dest.txt'));
        $this->assertFalse($this->storage->exists('move-src.txt'));
    }

    public function test_it_rejects_path_traversal() : void
    {
        $this->expectException(RuntimeException::class);
        $this->storage->put('../../../etc/passwd', 'hacked');
    }

    public function test_it_lists_files_in_directory() : void
    {
        $this->storage->put('a.txt', 'a');
        $this->storage->put('sub/b.txt', 'b');
        $files = $this->storage->files('');
        $this->assertContains('a.txt', $files);
        $this->assertContains('sub/b.txt', $files);
    }

    protected function setUp() : void
    {
        $this->tempDir = sys_get_temp_dir() . '/avax-fs-test-' . uniqid();
        $this->storage = new Local(['root' => $this->tempDir]);
    }

    protected function tearDown() : void
    {
        $this->removeDir($this->tempDir);
    }

    private function removeDir(string $dir) : void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        /** @var SplFileInfo $item */
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($dir);
    }
}

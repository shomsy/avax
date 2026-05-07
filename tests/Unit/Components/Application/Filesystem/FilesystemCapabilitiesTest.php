<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Filesystem;

use Avax\Components\Application\Filesystem\System\Capabilities\Disks\Local\LocalDisk;
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class FilesystemCapabilitiesTest extends TestCase
{
    private string     $tempDir;
    private Filesystem $filesystem;

    public function test_it_can_write_and_read_file() : void
    {
        $path    = 'test.txt';
        $content = 'Hello AvaX';

        $this->filesystem->write($path, $content);
        $this->assertTrue($this->filesystem->exists($path));
        $this->assertSame($content, $this->filesystem->read($path));
    }

    public function test_it_can_delete_file() : void
    {
        $path = 'test.txt';
        $this->filesystem->write($path, 'content');
        $this->assertTrue($this->filesystem->exists($path));

        $this->filesystem->delete($path);
        $this->assertFalse($this->filesystem->exists($path));
    }

    public function test_it_can_create_and_delete_directory() : void
    {
        $path = 'subdir';
        $this->filesystem->createDirectory($path);
        $this->assertTrue($this->filesystem->exists($path));

        $this->filesystem->deleteDirectory($path);
        $this->assertFalse($this->filesystem->exists($path));
    }

    public function test_it_throws_exception_when_reading_non_existent_file() : void
    {
        $path = 'missing.txt';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File not found or not readable');

        $this->filesystem->read($path);
    }

    /**
     * This test verifies that path traversal is rejected.
     */
    public function test_path_traversal_behavior() : void
    {
        $secretFile = sys_get_temp_dir() . '/avax_secret_' . uniqid();
        file_put_contents($secretFile, 'secret_content');

        // Traverse up from tempDir to access secretFile
        $traversalPath = '../' . basename($secretFile);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Path traversal attempt detected');

        try {
            $this->filesystem->read($traversalPath);
        } finally {
            if (file_exists($secretFile)) {
                unlink($secretFile);
            }
        }
    }

    protected function setUp() : void
    {
        $this->tempDir = realpath(sys_get_temp_dir()) . '/avax_filesystem_test_' . uniqid();
        mkdir($this->tempDir, 0755, true);
        $this->filesystem = new Filesystem(new LocalDisk($this->tempDir));
    }

    protected function tearDown() : void
    {
        $this->removeDirectory($this->tempDir);
    }

    private function removeDirectory(string $path) : void
    {
        if (! is_dir($path)) {
            return;
        }

        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$path/$file")) ? $this->removeDirectory("$path/$file") : unlink("$path/$file");
        }
        rmdir($path);
    }
}

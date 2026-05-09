<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Filesystem;

use Avax\Components\Application\Filesystem\System\Flows\AppendToFile\AppendToFile;
use Avax\Components\Application\Filesystem\System\Flows\CheckPathExists\CheckPathExists;
use Avax\Components\Application\Filesystem\System\Flows\ClearDirectory\ClearDirectory;
use Avax\Components\Application\Filesystem\System\Flows\CopyFile\CopyFile;
use Avax\Components\Application\Filesystem\System\Flows\CreateDirectory\CreateDirectory;
use Avax\Components\Application\Filesystem\System\Flows\DeleteDirectory\DeleteDirectory;
use Avax\Components\Application\Filesystem\System\Flows\DeleteFile\DeleteFile;
use Avax\Components\Application\Filesystem\System\Flows\ListDirectory\ListDirectory;
use Avax\Components\Application\Filesystem\System\Flows\MoveFile\MoveFile;
use Avax\Components\Application\Filesystem\System\Flows\ReadFile\ReadFile;
use Avax\Components\Application\Filesystem\System\Flows\WriteFile\WriteFile;
use Avax\Components\Application\Filesystem\System\Foundation\Failure\DirectoryNotFound;
use Avax\Components\Application\Filesystem\System\Foundation\Failure\FileNotFound;
use PHPUnit\Framework\TestCase;

final class FilesystemPublicSurfaceTest extends TestCase
{
    private string $tempDir;

    public function testReadFile() : void
    {
        $filePath = $this->tempDir . '/test.txt';
        file_put_contents($filePath, 'hello world');

        $reader  = new ReadFile();
        $content = $reader->execute($filePath);

        $this->assertSame('hello world', $content);
    }

    public function testReadFileThrowsOnNotFound() : void
    {
        $reader = new ReadFile();

        $this->expectException(FileNotFound::class);
        $reader->execute($this->tempDir . '/nonexistent.txt');
    }

    public function testWriteFile() : void
    {
        $filePath = $this->tempDir . '/written.txt';

        $writer = new WriteFile();
        $result = $writer->execute($filePath, 'test content');

        $this->assertTrue($result);
        $this->assertSame('test content', file_get_contents($filePath));
    }

    public function testWriteFileCreatesDirectory() : void
    {
        $filePath = $this->tempDir . '/subdir/nested/file.txt';

        $writer = new WriteFile();
        $result = $writer->execute($filePath, 'nested content');

        $this->assertTrue($result);
        $this->assertSame('nested content', file_get_contents($filePath));
    }

    public function testAppendToFile() : void
    {
        $filePath = $this->tempDir . '/append.txt';
        file_put_contents($filePath, 'initial');

        $appender = new AppendToFile();
        $appender->execute($filePath, ' more');

        $this->assertSame('initial more', file_get_contents($filePath));
    }

    public function testCopyFile() : void
    {
        $source = $this->tempDir . '/source.txt';
        $dest   = $this->tempDir . '/dest.txt';
        file_put_contents($source, 'copy me');

        $copier = new CopyFile();
        $result = $copier->execute($source, $dest);

        $this->assertTrue($result);
        $this->assertSame('copy me', file_get_contents($dest));
    }

    public function testCopyFileThrowsOnNotFound() : void
    {
        $copier = new CopyFile();

        $this->expectException(FileNotFound::class);
        $copier->execute($this->tempDir . '/nonexistent.txt', $this->tempDir . '/dest.txt');
    }

    public function testMoveFile() : void
    {
        $source = $this->tempDir . '/move_source.txt';
        $dest   = $this->tempDir . '/moved.txt';
        file_put_contents($source, 'move me');

        $mover  = new MoveFile();
        $result = $mover->execute($source, $dest);

        $this->assertTrue($result);
        $this->assertFalse(file_exists($source));
        $this->assertSame('move me', file_get_contents($dest));
    }

    public function testDeleteFile() : void
    {
        $filePath = $this->tempDir . '/delete_me.txt';
        file_put_contents($filePath, 'to be deleted');

        $deleter = new DeleteFile();
        $result  = $deleter->execute($filePath);

        $this->assertTrue($result);
        $this->assertFalse(file_exists($filePath));
    }

    public function testDeleteFileReturnsTrueForNonexistent() : void
    {
        $deleter = new DeleteFile();
        $result  = $deleter->execute($this->tempDir . '/already_gone.txt');

        $this->assertTrue($result);
    }

    public function testCreateDirectory() : void
    {
        $dirPath = $this->tempDir . '/newdir';

        $creator = new CreateDirectory();
        $result  = $creator->execute($dirPath);

        $this->assertTrue($result);
        $this->assertTrue(is_dir($dirPath));
    }

    public function testCreateDirectoryReturnsTrueIfExists() : void
    {
        $creator = new CreateDirectory();
        $result  = $creator->execute($this->tempDir);

        $this->assertTrue($result);
    }

    public function testDeleteDirectory() : void
    {
        $dirPath = $this->tempDir . '/to_delete';
        mkdir($dirPath, 0o755, true);

        $deleter = new DeleteDirectory();
        $result  = $deleter->execute($dirPath);

        $this->assertTrue($result);
        $this->assertFalse(is_dir($dirPath));
    }

    public function testDeleteDirectoryThrowsOnNonexistent() : void
    {
        $deleter = new DeleteDirectory();

        $this->expectException(DirectoryNotFound::class);
        $deleter->execute($this->tempDir . '/nonexistent');
    }

    public function testDeleteDirectoryReturnsFalseIfNotEmpty() : void
    {
        $dirPath = $this->tempDir . '/not_empty';
        mkdir($dirPath, 0o755, true);
        file_put_contents($dirPath . '/file.txt', 'content');

        $deleter = new DeleteDirectory();
        $result  = $deleter->execute($dirPath);

        $this->assertFalse($result);
        $this->assertTrue(is_dir($dirPath));
    }

    public function testClearDirectory() : void
    {
        $dirPath = $this->tempDir . '/to_clear';
        mkdir($dirPath, 0o755, true);
        file_put_contents($dirPath . '/file1.txt', 'content1');
        file_put_contents($dirPath . '/file2.txt', 'content2');
        mkdir($dirPath . '/subdir', 0o755, true);
        file_put_contents($dirPath . '/subdir/nested.txt', 'nested');

        $clearer = new ClearDirectory();
        $result  = $clearer->execute($dirPath);

        $this->assertTrue($result);
        $this->assertTrue(is_dir($dirPath));

        $items = array_filter(scandir($dirPath), fn ($item) => $item !== '.' && $item !== '..');
        $this->assertEmpty($items);
    }

    public function testListDirectory() : void
    {
        $dirPath = $this->tempDir . '/list_test';
        mkdir($dirPath, 0o755, true);
        file_put_contents($dirPath . '/aaa.txt', 'content');
        file_put_contents($dirPath . '/zzz.txt', 'content');
        mkdir($dirPath . '/subdir', 0o755, true);

        $lister = new ListDirectory();
        $items  = $lister->execute($dirPath);

        $this->assertCount(3, $items);
        $this->assertContains('aaa.txt', $items);
        $this->assertContains('zzz.txt', $items);
        $this->assertContains('subdir', $items);
    }

    public function testListDirectoryThrowsOnNonexistent() : void
    {
        $lister = new ListDirectory();

        $this->expectException(DirectoryNotFound::class);
        $lister->execute($this->tempDir . '/nonexistent');
    }

    public function testCheckPathExists() : void
    {
        $checker = new CheckPathExists();

        file_put_contents($this->tempDir . '/existing.txt', 'content');
        $this->assertTrue($checker->execute($this->tempDir . '/existing.txt'));

        $this->assertFalse($checker->execute($this->tempDir . '/nonexistent.txt'));
    }

    protected function setUp() : void
    {
        $this->tempDir = sys_get_temp_dir() . '/avax_test_' . uniqid();
        mkdir($this->tempDir, 0o755, true);
    }

    protected function tearDown() : void
    {
        $this->removeDirectoryRecursive($this->tempDir);
    }

    private function removeDirectoryRecursive(string $path) : void
    {
        if (! is_dir($path)) {
            return;
        }

        $items = scandir($path);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $itemPath = $path . '/' . $item;

            if (is_dir($itemPath)) {
                $this->removeDirectoryRecursive($itemPath);
            } else {
                unlink($itemPath);
            }
        }

        rmdir($path);
    }
}
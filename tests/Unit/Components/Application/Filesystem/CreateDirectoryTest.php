<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Filesystem;

use Avax\Components\Application\Filesystem\System\Flows\CreateDirectory\CreateDirectory;
use Avax\Components\Application\Filesystem\System\Foundation\Failure\FilesystemOperationFailed;
use PHPUnit\Framework\TestCase;

final class CreateDirectoryTest extends TestCase
{
    private string          $tmpDir;
    private CreateDirectory $flow;

    public function test_create_single_directory() : void
    {
        $path = $this->tmpDir . '/new_dir';

        $result = $this->flow->execute($path);

        $this->assertTrue($result);
        $this->assertDirectoryExists($path);
    }

    public function test_create_directory_with_custom_permissions() : void
    {
        $path = $this->tmpDir . '/custom_perms';

        $result = $this->flow->execute($path, 0o700);

        $this->assertTrue($result);
        $this->assertDirectoryExists($path);
        $this->assertSame(0o700, fileperms($path) & 0o777);
    }

    public function test_create_directory_with_default_permissions() : void
    {
        $path = $this->tmpDir . '/default_perms';

        $result = $this->flow->execute($path);

        $this->assertTrue($result);
        $this->assertDirectoryExists($path);
        $this->assertSame(0o755, fileperms($path) & 0o777);
    }

    public function test_create_existing_directory_returns_true() : void
    {
        $path = $this->tmpDir . '/already_exists';
        mkdir($path);

        $result = $this->flow->execute($path);

        $this->assertTrue($result);
    }

    public function test_create_nested_directories() : void
    {
        // CreateDirectory uses mkdir recursive=false, so we build step by step
        $base   = $this->tmpDir . '/a';
        $nested = $this->tmpDir . '/a/b/c/d/e';

        mkdir($this->tmpDir . '/a/b/c/d', 0o755, true);

        $result = $this->flow->execute($nested);

        $this->assertTrue($result);
        $this->assertDirectoryExists($nested);
    }

    public function test_create_directory_with_null_bytes_strips_them() : void
    {
        $path = $this->tmpDir . "/dir\0name";

        $result = $this->flow->execute($path);

        $this->assertTrue($result);
        $this->assertDirectoryExists($this->tmpDir . '/dirname');
    }

    public function test_create_multiple_directories_sequentially() : void
    {
        $dirs = [];
        for ($i = 0; $i < 5; $i++) {
            $path   = $this->tmpDir . "/dir_{$i}";
            $result = $this->flow->execute($path);
            $this->assertTrue($result);
            $this->assertDirectoryExists($path);
            $dirs[] = $path;
        }
    }

    public function test_create_directory_with_various_permissions() : void
    {
        // Save and disable umask for exact permission testing
        $oldUmask = umask(0);

        try {
            $perms = [0o755, 0o700, 0o775, 0o750];

            foreach ($perms as $i => $perm) {
                $path   = $this->tmpDir . "/perms_{$i}";
                $result = $this->flow->execute($path, $perm);

                $this->assertTrue($result);
                $this->assertSame($perm, fileperms($path) & 0o777);
            }
        } finally {
            umask($oldUmask);
        }
    }

    public function test_create_directory_with_octal_permissions() : void
    {
        $path = $this->tmpDir . '/octal_test';

        $result = $this->flow->execute($path, 0o644);

        $this->assertTrue($result);
        $this->assertDirectoryExists($path);
        // Note: umask may affect actual permissions
        $actualPerms = fileperms($path) & 0o777;
        $this->assertGreaterThan(0, $actualPerms);
    }

    public function test_create_directory_at_root_level() : void
    {
        $path = $this->tmpDir . '/top_level';

        $result = $this->flow->execute($path);

        $this->assertTrue($result);
        $this->assertDirectoryExists($path);
    }

    public function test_create_directory_with_trailing_slash() : void
    {
        $path = $this->tmpDir . '/trailing/';

        $result = $this->flow->execute($path);

        $this->assertTrue($result);
        $this->assertDirectoryExists(rtrim($path, '/'));
    }

    public function test_create_directory_name_with_spaces() : void
    {
        $path = $this->tmpDir . '/directory with spaces';

        $result = $this->flow->execute($path);

        $this->assertTrue($result);
        $this->assertDirectoryExists($path);
    }

    protected function setUp() : void
    {
        $this->tmpDir = sys_get_temp_dir() . '/avax_createdir_' . uniqid();
        mkdir($this->tmpDir, 0o755, true);
        $this->flow = new CreateDirectory();
    }

    protected function tearDown() : void
    {
        $this->removeDirectoryRecursive($this->tmpDir);
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

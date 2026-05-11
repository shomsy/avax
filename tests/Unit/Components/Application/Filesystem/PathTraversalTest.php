<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Filesystem;

use Avax\Components\Application\Filesystem\System\Capabilities\LocalPaths\EnsurePathIsInsideRoot;
use Avax\Components\Application\Filesystem\System\Capabilities\LocalPaths\NormalizePath;
use Avax\Components\Application\Filesystem\System\Capabilities\LocalPaths\RejectPathTraversal;
use Avax\Components\Application\Filesystem\System\Foundation\Failure\PathTraversalAttempt;
use PHPUnit\Framework\TestCase;

final class PathTraversalTest extends TestCase
{
    private string $tmpDir;

    public function test_reject_path_traversal_accepts_safe_path() : void
    {
        $checker = new RejectPathTraversal();

        $checker->execute('/safe/path/file.txt');
        $checker->execute('relative/path/file.txt');
        $checker->execute('/');

        $this->assertInstanceOf(RejectPathTraversal::class, $checker);
    }

    public function test_reject_path_traversal_rejects_double_dot() : void
    {
        $checker = new RejectPathTraversal();

        $this->expectException(PathTraversalAttempt::class);
        $checker->execute('/safe/../../../etc/passwd');
    }

    /* ==================== RejectPathTraversal Tests ==================== */

    public function test_reject_path_traversal_rejects_double_dot_at_start() : void
    {
        $checker = new RejectPathTraversal();

        $this->expectException(PathTraversalAttempt::class);
        $checker->execute('../etc/passwd');
    }

    public function test_reject_path_traversal_rejects_double_dot_in_middle() : void
    {
        $checker = new RejectPathTraversal();

        $this->expectException(PathTraversalAttempt::class);
        $checker->execute('/safe/../secret/file.txt');
    }

    public function test_reject_path_traversal_rejects_null_byte() : void
    {
        $checker = new RejectPathTraversal();

        $this->expectException(PathTraversalAttempt::class);
        $checker->execute("/safe\0/file.txt");
    }

    public function test_reject_path_traversal_rejects_newline() : void
    {
        $checker = new RejectPathTraversal();

        $this->expectException(PathTraversalAttempt::class);
        $checker->execute("/safe\n/file.txt");
    }

    public function test_reject_path_traversal_rejects_carriage_return() : void
    {
        $checker = new RejectPathTraversal();

        $this->expectException(PathTraversalAttempt::class);
        $checker->execute("/safe\r/file.txt");
    }

    public function test_reject_path_traversal_rejects_tilde_expansion() : void
    {
        $checker = new RejectPathTraversal();

        $this->expectException(PathTraversalAttempt::class);
        $checker->execute('~/secret/file.txt');
    }

    public function test_reject_path_traversal_rejects_backslash_normalized() : void
    {
        $checker = new RejectPathTraversal();

        // Backslashes are converted to forward slashes, then '..' is detected
        $this->expectException(PathTraversalAttempt::class);
        $checker->execute('..\\..\\etc\\passwd');
    }

    public function test_reject_path_traversal_exception_contains_path() : void
    {
        $checker       = new RejectPathTraversal();
        $maliciousPath = '../../../etc/passwd';

        try {
            $checker->execute($maliciousPath);
            $this->fail('Expected PathTraversalAttempt');
        } catch (PathTraversalAttempt $e) {
            $this->assertSame($maliciousPath, $e->path);
        }
    }

    public function test_ensure_path_inside_root_allows_safe_path() : void
    {
        $checker  = new EnsurePathIsInsideRoot();
        $safePath = $this->tmpDir . '/safe/file.txt';

        $checker->execute($safePath, $this->tmpDir);

        $this->assertInstanceOf(EnsurePathIsInsideRoot::class, $checker);
    }

    public function test_ensure_path_inside_root_rejects_path_outside_root() : void
    {
        $checker = new EnsurePathIsInsideRoot();

        // The implementation does: realpath(dirname($root) . '/' . ltrim($path, '/'))
        // So we create a sibling file to the root, and reference it as a relative path
        $siblingFile = dirname($this->tmpDir) . '/sibling_outside_' . uniqid() . '.txt';
        file_put_contents($siblingFile, 'outside');

        try {
            $this->expectException(PathTraversalAttempt::class);
            $checker->execute(basename($siblingFile), $this->tmpDir);
        } finally {
            if (file_exists($siblingFile)) {
                unlink($siblingFile);
            }
        }
    }

    /* ==================== EnsurePathIsInsideRoot Tests ==================== */

    public function test_ensure_path_inside_root_returns_when_root_does_not_exist() : void
    {
        $checker = new EnsurePathIsInsideRoot();

        $checker->execute('/some/path', '/nonexistent/root');

        $this->assertInstanceOf(EnsurePathIsInsideRoot::class, $checker);
    }

    public function test_ensure_path_inside_root_allows_nested_path_within_root() : void
    {
        $nested = $this->tmpDir . '/a/b/c';
        mkdir($nested, 0o755, true);
        $checker = new EnsurePathIsInsideRoot();

        $checker->execute($nested, $this->tmpDir);

        $this->assertInstanceOf(EnsurePathIsInsideRoot::class, $checker);
    }

    public function test_ensure_path_inside_root_exception_contains_path() : void
    {
        $checker = new EnsurePathIsInsideRoot();

        $siblingFile = dirname($this->tmpDir) . '/sibling_exc_' . uniqid() . '.txt';
        file_put_contents($siblingFile, 'outside');
        $relativeRef = basename($siblingFile);

        try {
            try {
                $checker->execute($relativeRef, $this->tmpDir);
                $this->fail('Expected PathTraversalAttempt');
            } catch (PathTraversalAttempt $e) {
                $this->assertSame($relativeRef, $e->path);
            }
        } finally {
            if (file_exists($siblingFile)) {
                unlink($siblingFile);
            }
        }
    }

    public function test_normalize_path_single_path() : void
    {
        $normalizer = new NormalizePath();

        $result = $normalizer->execute('/safe/file.txt');

        $this->assertSame('/safe/file.txt', $result);
    }

    public function test_normalize_path_removes_double_dots() : void
    {
        $normalizer = new NormalizePath();

        $result = $normalizer->execute('/a/b/../c');

        $this->assertSame('/a/c', $result);
    }

    /* ==================== NormalizePath Tests ==================== */

    public function test_normalize_path_removes_current_directory_dots() : void
    {
        $normalizer = new NormalizePath();

        $result = $normalizer->execute('/a/./b/./c');

        $this->assertSame('/a/b/c', $result);
    }

    public function test_normalize_path_collapses_multiple_slashes() : void
    {
        $normalizer = new NormalizePath();

        $result = $normalizer->execute('/a///b////c');

        $this->assertSame('/a/b/c', $result);
    }

    public function test_normalize_path_converts_backslashes() : void
    {
        $normalizer = new NormalizePath();

        $result = $normalizer->execute('\\a\\b\\c');

        $this->assertSame('/a/b/c', $result);
    }

    public function test_normalize_path_empty_returns_root() : void
    {
        $normalizer = new NormalizePath();

        $result = $normalizer->execute('');

        $this->assertSame('/', $result);
    }

    public function test_normalize_path_relative_path() : void
    {
        $normalizer = new NormalizePath();

        $result = $normalizer->execute('relative/path');

        $this->assertSame('relative/path', $result);
    }

    public function test_normalize_path_multiple_double_dots() : void
    {
        $normalizer = new NormalizePath();

        $result = $normalizer->execute('/a/b/c/../../d');

        $this->assertSame('/a/d', $result);
    }

    public function test_normalize_path_double_dot_beyond_root() : void
    {
        $normalizer = new NormalizePath();

        $result = $normalizer->execute('/../../../a');

        $this->assertSame('/a', $result);
    }

    public function test_normalize_path_preserves_trailing_slash_removal() : void
    {
        $normalizer = new NormalizePath();

        $result = $normalizer->execute('/a/b/');

        // The normalize strips trailing components through the stack logic
        $this->assertSame('/a/b', $result);
    }

    public function test_normalize_path_only_dots() : void
    {
        $normalizer = new NormalizePath();

        $result = $normalizer->execute('././.');

        // Relative path with only dots resolves to empty stack, no leading slash
        $this->assertSame('', $result);
    }

    public function test_normalize_path_only_double_dots() : void
    {
        $normalizer = new NormalizePath();

        $result = $normalizer->execute('../../..');

        // Relative path with only double dots resolves to empty stack
        $this->assertSame('', $result);
    }

    public function test_normalize_path_windows_style() : void
    {
        $normalizer = new NormalizePath();

        $result = $normalizer->execute('C:\\Users\\test\\file.txt');

        // Relative path (no leading /) stays without leading slash after normalization
        $this->assertSame('C:/Users/test/file.txt', $result);
    }

    public function test_reject_then_normalize_safe_path() : void
    {
        $checker    = new RejectPathTraversal();
        $normalizer = new NormalizePath();

        // Safe path passes rejection
        $checker->execute('/safe/nested/file.txt');

        // Then normalizes correctly
        $result = $normalizer->execute('/safe/../nested/./file.txt');

        $this->assertSame('/nested/file.txt', $result);
    }

    public function test_reject_blocks_before_normalize_for_malicious_path() : void
    {
        $checker = new RejectPathTraversal();

        $this->expectException(PathTraversalAttempt::class);
        $checker->execute('/safe/../../../etc/passwd');
    }

    /* ==================== Integration: Traversal Defense ==================== */

    protected function setUp() : void
    {
        $this->tmpDir = sys_get_temp_dir() . '/avax_path_traversal_' . uniqid();
        mkdir($this->tmpDir, 0o755, true);
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

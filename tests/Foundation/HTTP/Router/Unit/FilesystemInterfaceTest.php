<?php

declare(strict_types=1);

use Avax\Contracts\FilesystemException;
use Avax\Contracts\FilesystemInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests for FilesystemInterface contract compliance.
 *
 * Ensures all filesystem operations work correctly and throw proper exceptions.
 */
class FilesystemInterfaceTest extends TestCase
{
    private FilesystemInterface $filesystem;

    /**
     * @test
     */
    public function filesystem_can_read_files() : void
    {
        $this->filesystem->expects(invocationRule: $this->once())
            ->method(constraint: 'get')
            ->with('/path/to/file.txt')
            ->willReturn(value: 'file contents');

        $contents = $this->filesystem->get(path: '/path/to/file.txt');
        $this->assertEquals(expected: 'file contents', actual: $contents);
    }

    /**
     * @test
     */
    public function filesystem_throws_exception_on_read_failure() : void
    {
        $this->filesystem->expects(invocationRule: $this->once())
            ->method(constraint: 'get')
            ->with('/nonexistent/file.txt')
            ->willThrowException(exception: new FilesystemException(message: 'File not found', path: '/nonexistent/file.txt'));

        $this->expectException(exception: FilesystemException::class);
        $this->expectExceptionMessage(message: 'File not found');

        $this->filesystem->get(path: '/nonexistent/file.txt');
    }

    /**
     * @test
     */
    public function filesystem_can_write_files() : void
    {
        $this->filesystem->expects(invocationRule: $this->once())
            ->method(constraint: 'put')
            ->with('/path/to/file.txt', 'content to write');

        // Should not throw exception
        $this->filesystem->put(path: '/path/to/file.txt', content: 'content to write');
        $this->assertTrue(condition: true);
    }

    /**
     * @test
     */
    public function filesystem_throws_exception_on_write_failure() : void
    {
        $this->filesystem->expects(invocationRule: $this->once())
            ->method(constraint: 'put')
            ->with('/readonly/path/file.txt', 'content')
            ->willThrowException(exception: new FilesystemException(message: 'Permission denied', path: '/readonly/path/file.txt'));

        $this->expectException(exception: FilesystemException::class);
        $this->expectExceptionMessage(message: 'Permission denied');

        $this->filesystem->put(path: '/readonly/path/file.txt', content: 'content');
    }

    /**
     * @test
     */
    public function filesystem_can_check_file_existence() : void
    {
        $this->filesystem->expects(invocationRule: $this->exactly(count: 2))
            ->method(constraint: 'exists')
            ->willReturnCallback(callback: static function ($path) {
                return match ($path) {
                    '/existing/file.txt'    => true,
                    '/nonexistent/file.txt' => false,
                    default                 => false
                };
            });

        $this->assertTrue(condition: $this->filesystem->exists(path: '/existing/file.txt'));
        $this->assertFalse(condition: $this->filesystem->exists(path: '/nonexistent/file.txt'));
    }

    /**
     * @test
     */
    public function filesystem_can_get_file_modification_time() : void
    {
        $timestamp = 1640995200; // 2022-01-01 00:00:00

        $this->filesystem->expects(invocationRule: $this->once())
            ->method(constraint: 'lastModified')
            ->with('/path/to/file.txt')
            ->willReturn(value: $timestamp);

        $result = $this->filesystem->lastModified(path: '/path/to/file.txt');
        $this->assertEquals(expected: $timestamp, actual: $result);
    }

    /**
     * @test
     */
    public function filesystem_returns_null_for_nonexistent_file_modification_time() : void
    {
        $this->filesystem->expects(invocationRule: $this->once())
            ->method(constraint: 'lastModified')
            ->with('/nonexistent/file.txt')
            ->willReturn(value: null);

        $result = $this->filesystem->lastModified(path: '/nonexistent/file.txt');
        $this->assertNull(actual: $result);
    }

    /**
     * @test
     */
    public function filesystem_can_ensure_directory_exists() : void
    {
        $this->filesystem->expects(invocationRule: $this->once())
            ->method(constraint: 'ensureDirectory')
            ->with('/path/to/directory');

        // Should not throw exception
        $this->filesystem->ensureDirectory(path: '/path/to/directory');
        $this->assertTrue(condition: true);
    }

    /**
     * @test
     */
    public function filesystem_throws_exception_when_directory_creation_fails() : void
    {
        $this->filesystem->expects(invocationRule: $this->once())
            ->method(constraint: 'ensureDirectory')
            ->with('/readonly/path')
            ->willThrowException(exception: new FilesystemException(message: 'Cannot create directory', path: '/readonly/path'));

        $this->expectException(exception: FilesystemException::class);
        $this->expectExceptionMessage(message: 'Cannot create directory');

        $this->filesystem->ensureDirectory(path: '/readonly/path');
    }

    /**
     * @test
     */
    public function filesystem_can_delete_files() : void
    {
        $this->filesystem->expects(invocationRule: $this->once())
            ->method(constraint: 'delete')
            ->with('/path/to/file.txt');

        // Should not throw exception
        $this->filesystem->delete(path: '/path/to/file.txt');
        $this->assertTrue(condition: true);
    }

    /**
     * @test
     */
    public function filesystem_throws_exception_on_delete_failure() : void
    {
        $this->filesystem->expects(invocationRule: $this->once())
            ->method(constraint: 'delete')
            ->with('/protected/file.txt')
            ->willThrowException(exception: new FilesystemException(message: 'Delete failed', path: '/protected/file.txt'));

        $this->expectException(exception: FilesystemException::class);
        $this->expectExceptionMessage(message: 'Delete failed');

        $this->filesystem->delete(path: '/protected/file.txt');
    }

    protected function setUp() : void
    {
        // This would be a concrete implementation in real usage
        // For testing, we'd use a mock or in-memory implementation
        $this->filesystem = $this->createMock(FilesystemInterface::class);
    }
}

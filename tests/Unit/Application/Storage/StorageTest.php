<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Application\Storage;

use Avax\Components\Application\Storage\System\Capabilities\Disks\LocalDisk\LocalDisk;
use Avax\Components\Application\Storage\System\Capabilities\Disks\MemoryDisk\MemoryDisk;
use Avax\Components\Application\Storage\System\Foundation\Failure\DiskNotFound;
use Avax\Components\Application\Storage\System\Foundation\Failure\StoredObjectNotFound;
use Avax\Components\Application\Storage\System\Foundation\Failure\TemporaryUrlNotSupported;
use Avax\Components\Application\Storage\System\Foundation\Values\StoragePath;
use Avax\Components\Application\Storage\System\PublicSurface\Storage;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class StorageTest extends TestCase
{
    protected function tearDown() : void
    {
        Storage::setDefaultDisk('local');
        Storage::registerDisk('local', new MemoryDisk());
    }

    // -- MemoryDisk unit tests

    public function test_memory_disk_write_and_read() : void
    {
        $disk = new MemoryDisk();
        $disk->write(new StoragePath('hello.txt'), 'world');

        self::assertSame('world', $disk->read(new StoragePath('hello.txt')));
    }

    public function test_memory_disk_exists() : void
    {
        $disk = new MemoryDisk();
        $disk->write(new StoragePath('file.txt'), 'content');

        self::assertTrue($disk->exists(new StoragePath('file.txt')));
        self::assertFalse($disk->exists(new StoragePath('missing.txt')));
    }

    public function test_memory_disk_delete() : void
    {
        $disk = new MemoryDisk();
        $disk->write(new StoragePath('file.txt'), 'content');

        self::assertTrue($disk->delete(new StoragePath('file.txt')));
        self::assertFalse($disk->exists(new StoragePath('file.txt')));
        self::assertFalse($disk->delete(new StoragePath('missing.txt')));
    }

    public function test_memory_disk_copy() : void
    {
        $disk = new MemoryDisk();
        $disk->write(new StoragePath('src.txt'), 'data');

        self::assertTrue($disk->copy(new StoragePath('src.txt'), new StoragePath('dest.txt')));
        self::assertSame('data', $disk->read(new StoragePath('dest.txt')));
        self::assertSame('data', $disk->read(new StoragePath('src.txt')));
    }

    public function test_memory_disk_move() : void
    {
        $disk = new MemoryDisk();
        $disk->write(new StoragePath('src.txt'), 'data');

        self::assertTrue($disk->move(new StoragePath('src.txt'), new StoragePath('dest.txt')));
        self::assertSame('data', $disk->read(new StoragePath('dest.txt')));
        self::assertFalse($disk->exists(new StoragePath('src.txt')));
    }

    public function test_memory_disk_read_not_found_throws() : void
    {
        $disk = new MemoryDisk();

        $this->expectException(StoredObjectNotFound::class);
        $disk->read(new StoragePath('missing.txt'));
    }

    public function test_memory_disk_url() : void
    {
        $disk = new MemoryDisk();

        self::assertSame('memory://hello.txt', $disk->url(new StoragePath('hello.txt')));
    }

    public function test_memory_disk_temporary_url_not_supported() : void
    {
        $disk = new MemoryDisk();

        $this->expectException(TemporaryUrlNotSupported::class);
        $disk->temporaryUrl(new StoragePath('hello.txt'), new DateTimeImmutable('+1 hour'));
    }

    public function test_memory_disk_supports_temporary_url_is_false() : void
    {
        $disk = new MemoryDisk();

        self::assertFalse($disk->supportsTemporaryUrl());
    }

    public function test_memory_disk_clear() : void
    {
        $disk = new MemoryDisk();
        $disk->write(new StoragePath('a.txt'), 'a');
        $disk->write(new StoragePath('b.txt'), 'b');
        $disk->clear();

        self::assertFalse($disk->exists(new StoragePath('a.txt')));
        self::assertFalse($disk->exists(new StoragePath('b.txt')));
    }

    public function test_memory_disk_paths() : void
    {
        $disk = new MemoryDisk();
        $disk->write(new StoragePath('a.txt'), 'a');
        $disk->write(new StoragePath('b/c.txt'), 'c');

        $paths = $disk->paths();

        self::assertCount(2, $paths);
        self::assertContains('a.txt', $paths);
        self::assertContains('b/c.txt', $paths);
    }

    // -- Storage facade tests

    public function test_storage_put_and_get() : void
    {
        Storage::registerDisk('local', new MemoryDisk());

        Storage::put('test.txt', 'hello storage');

        self::assertSame('hello storage', Storage::get('test.txt'));
    }

    public function test_storage_exists() : void
    {
        Storage::registerDisk('local', new MemoryDisk());
        Storage::put('exists.txt', 'yes');

        self::assertTrue(Storage::exists('exists.txt'));
        self::assertFalse(Storage::exists('nope.txt'));
    }

    public function test_storage_delete() : void
    {
        Storage::registerDisk('local', new MemoryDisk());
        Storage::put('delete.txt', 'bye');

        Storage::delete('delete.txt');

        self::assertFalse(Storage::exists('delete.txt'));
    }

    public function test_storage_copy() : void
    {
        Storage::registerDisk('local', new MemoryDisk());
        Storage::put('src.txt', 'copy me');

        Storage::copy('src.txt', 'dest.txt');

        self::assertSame('copy me', Storage::get('dest.txt'));
    }

    public function test_storage_move() : void
    {
        Storage::registerDisk('local', new MemoryDisk());
        Storage::put('src.txt', 'move me');

        Storage::move('src.txt', 'dest.txt');

        self::assertSame('move me', Storage::get('dest.txt'));
        self::assertFalse(Storage::exists('src.txt'));
    }

    public function test_storage_url() : void
    {
        Storage::registerDisk('local', new MemoryDisk());

        self::assertSame('memory://hello.txt', Storage::url('hello.txt'));
    }

    public function test_storage_disk_not_found_throws() : void
    {
        $this->expectException(DiskNotFound::class);
        Storage::disk('nonexistent');
    }

    public function test_storage_register_and_get_disk() : void
    {
        $memory = new MemoryDisk();
        Storage::registerDisk('custom', $memory);

        self::assertSame($memory, Storage::disk('custom'));
    }
}

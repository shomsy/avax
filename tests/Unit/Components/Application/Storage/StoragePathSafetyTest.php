<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Storage;

use Avax\Components\Application\Storage\System\Capabilities\Disks\MemoryDisk\MemoryDisk;
use Avax\Components\Application\Storage\System\Capabilities\Visibility\ObjectVisibility;
use Avax\Components\Application\Storage\System\Capabilities\Visibility\ResolveObjectVisibility;
use Avax\Components\Application\Storage\System\Foundation\Failure\TemporaryUrlNotSupported;
use Avax\Components\Application\Storage\System\Foundation\Values\StoragePath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MemoryDisk::class)]
#[CoversClass(ResolveObjectVisibility::class)]
final class StoragePathSafetyTest extends TestCase
{
    public function test_memory_disk_basic(): void
    {
        $memoryDisk = new MemoryDisk();
        $path = new StoragePath('test.txt');

        $memoryDisk->write($path, 'hello');
        self::assertTrue($memoryDisk->exists($path));
        self::assertSame('hello', $memoryDisk->read($path));
    }

    public function test_memory_disk_delete(): void
    {
        $memoryDisk = new MemoryDisk();
        $path = new StoragePath('delete.txt');
        $memoryDisk->write($path, 'data');

        $memoryDisk->delete($path);
        self::assertFalse($memoryDisk->exists($path));
    }

    public function test_memory_disk_copy(): void
    {
        $memoryDisk = new MemoryDisk();
        $src = new StoragePath('src.txt');
        $dst = new StoragePath('dst.txt');
        $memoryDisk->write($src, 'content');

        $memoryDisk->copy($src, $dst);

        self::assertTrue($memoryDisk->exists($dst));
        self::assertSame('content', $memoryDisk->read($dst));
    }

    public function test_memory_disk_url(): void
    {
        $memoryDisk = new MemoryDisk();
        $path = new StoragePath('file.txt');

        $url = $memoryDisk->url($path);
        self::assertSame('memory://file.txt', $url);
    }

    public function test_memory_disk_temporary_url_not_supported(): void
    {
        $memoryDisk = new MemoryDisk();

        $this->expectException(TemporaryUrlNotSupported::class);
        $memoryDisk->temporaryUrl(new StoragePath('test.txt'), new \DateTimeImmutable('+1 hour'));
    }

    public function test_visibility_resolver_public(): void
    {
        $resolver = new ResolveObjectVisibility();
        $result = $resolver->execute('public');

        self::assertSame(ObjectVisibility::PUBLIC, $result);
    }

    public function test_visibility_resolver_private(): void
    {
        $resolver = new ResolveObjectVisibility();
        $result = $resolver->execute('private');

        self::assertSame(ObjectVisibility::PRIVATE, $result);
    }

    public function test_visibility_resolver_default(): void
    {
        $resolver = new ResolveObjectVisibility();
        $result = $resolver->execute(null);

        self::assertSame(ObjectVisibility::PRIVATE, $result);
    }

    public function test_memory_disk_clear(): void
    {
        $memoryDisk = new MemoryDisk();
        $memoryDisk->write(new StoragePath('a.txt'), 'a');
        $memoryDisk->write(new StoragePath('b.txt'), 'b');

        $memoryDisk->clear();

        self::assertSame([], $memoryDisk->paths());
    }
}

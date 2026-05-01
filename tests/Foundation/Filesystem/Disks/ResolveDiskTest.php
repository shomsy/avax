<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Disks;

use Avax\Filesystem\Configuration\FilesystemConfig;
use Avax\Filesystem\Disks\InvalidDiskDriver;
use Avax\Filesystem\Disks\Local\LocalDisk;
use Avax\Filesystem\Disks\ResolveDisk;
use Avax\Tests\TestCase;

class ResolveDiskTest extends TestCase
{
    public function test_execute_returns_local_disk() : void
    {
        $result = new ResolveDisk()->execute(name: 'local');

        self::assertInstanceOf(LocalDisk::class, $result);
    }

    public function test_execute_throws_exception_for_unsupported_driver() : void
    {
        $this->expectException(exception: InvalidDiskDriver::class);
        $this->expectExceptionMessage(message: 'Unsupported disk driver:');

        new ResolveDisk()->execute(name: 'unknown');
    }

    public function test_execute_with_null_returns_local_disk() : void
    {
        $result = new ResolveDisk()->execute(name: null);

        self::assertInstanceOf(LocalDisk::class, $result);
    }

    public function test_execute_uses_configured_default_disk() : void
    {
        $resolver = new ResolveDisk(
            config: new FilesystemConfig(
                        default: 'media',
                        disks  : ['media' => ['driver' => 'local']],
                    ),
        );

        self::assertInstanceOf(LocalDisk::class, $resolver->execute());
    }
}

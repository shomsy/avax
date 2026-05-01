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
    public function testExecuteReturnsLocalDisk() : void
    {
        $result = new ResolveDisk()->execute(name: 'local');

        self::assertInstanceOf(LocalDisk::class, $result);
    }

    public function testExecuteThrowsExceptionForUnsupportedDriver() : void
    {
        $this->expectException(exception: InvalidDiskDriver::class);
        $this->expectExceptionMessage(message: 'Unsupported disk driver:');

        new ResolveDisk()->execute(name: 'unknown');
    }

    public function testExecuteWithNullReturnsLocalDisk() : void
    {
        $result = new ResolveDisk()->execute(name: null);

        self::assertInstanceOf(LocalDisk::class, $result);
    }

    public function testExecuteUsesConfiguredDefaultDisk() : void
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

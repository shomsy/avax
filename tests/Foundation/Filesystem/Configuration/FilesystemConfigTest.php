<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Configuration;

use Avax\Filesystem\Configuration\FilesystemConfig;
use Avax\Tests\TestCase;

class FilesystemConfigTest extends TestCase
{
    public function testDefaultsCreatesConfig() : void
    {
        $config = FilesystemConfig::defaults();

        self::assertSame(expected: 'local', actual: $config->default);
        self::assertArrayHasKey(key: 'local', array: $config->disks);
    }

    public function testDiskReturnsNullForUnknown() : void
    {
        $config = FilesystemConfig::defaults();

        $result = $config->disk(name: 'unknown');

        self::assertNull(actual: $result);
    }

    public function testDiskReturnsDiskConfig() : void
    {
        $config = FilesystemConfig::defaults();

        $result = $config->disk(name: 'local');

        self::assertSame(expected: ['driver' => 'local'], actual: $result);
    }

    public function testCustomConfig() : void
    {
        $config = new FilesystemConfig(
            default: 's3',
            disks  : ['s3' => ['driver' => 's3', 'bucket' => 'test']],
        );

        self::assertSame(expected: 's3', actual: $config->default);
        self::assertSame(expected: ['driver' => 's3', 'bucket' => 'test'], actual: $config->disk(name: 's3'));
    }
}

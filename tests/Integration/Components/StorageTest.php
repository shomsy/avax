<?php

declare(strict_types=1);

namespace Avax\Tests\Integration\Components;

use Avax\Components\Operations\Filesystem\System\Capabilities\Adapters\LocalStorageAdapter;
use Avax\Tests\TestCase;

final class StorageTest extends TestCase
{
    public function test_storage_adapter_handles_directory_lifecycle(): void
    {
        $storage = new LocalStorageAdapter(config: ['root' => sys_get_temp_dir().'/avax-storage-integration-'.uniqid()]);

        $storage->put(path: 'nested/file.txt', contents: 'payload');

        self::assertSame(expected: ['nested/file.txt'], actual: $storage->files(directory: 'nested'));
        self::assertTrue(condition: $storage->deleteDirectory(directory: 'nested'));
    }
}

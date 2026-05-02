<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Enterprise;

use Avax\Components\Operations\Filesystem\System\Capabilities\Adapters\LocalStorageAdapter;
use Avax\Components\Operations\Filesystem\System\Capabilities\Adapters\S3StorageAdapter;
use Avax\Components\Operations\Filesystem\System\Capabilities\Adapters\Storage;
use Avax\Tests\TestCase;
use RuntimeException;

final class StorageUnitTest extends TestCase
{
    public function test_local_storage_writes_and_reads_files() : void
    {
        $storage = $this->localStorage();

        $storage->put(path: 'docs/readme.txt', contents: 'hello');

        self::assertSame(expected: 'hello', actual: $storage->get(path: 'docs/readme.txt'));
    }

    private function localStorage() : LocalStorageAdapter
    {
        return new LocalStorageAdapter(config: ['root' => sys_get_temp_dir() . '/avax-storage-' . uniqid()]);
    }

    public function test_local_storage_reports_file_existence() : void
    {
        $storage = $this->localStorage();

        $storage->put(path: 'exists.txt', contents: 'yes');

        self::assertTrue(condition: $storage->exists(path: 'exists.txt'));
    }

    public function test_local_storage_deletes_files() : void
    {
        $storage = $this->localStorage();
        $storage->put(path: 'delete.txt', contents: 'x');

        $storage->delete(path: 'delete.txt');

        self::assertFalse(condition: $storage->exists(path: 'delete.txt'));
    }

    public function test_local_storage_reports_size() : void
    {
        $storage = $this->localStorage();
        $storage->put(path: 'size.txt', contents: '1234');

        self::assertSame(expected: 4, actual: $storage->size(path: 'size.txt'));
    }

    public function test_local_storage_lists_directory_files() : void
    {
        $storage = $this->localStorage();
        $storage->put(path: 'list/a.txt', contents: 'a');
        $storage->put(path: 'list/b.txt', contents: 'b');

        self::assertSame(expected: ['list/a.txt', 'list/b.txt'], actual: $storage->files(directory: 'list'));
    }

    public function test_local_storage_generates_signed_urls() : void
    {
        $url = $this->localStorage()->signedUrl(path: 'private.txt', expiresInSeconds: 120);

        self::assertStringContainsString(needle: 'signature=', haystack: $url);
    }

    public function test_local_storage_rejects_path_traversal() : void
    {
        $this->expectException(exception: RuntimeException::class);

        $this->localStorage()->put(path: '../escape.txt', contents: 'bad');
    }

    public function test_s3_storage_generates_bucket_url() : void
    {
        $storage = new S3StorageAdapter(config: ['bucket' => 'avax-test', 'region' => 'eu-central-1']);

        self::assertSame(expected: 'https://avax-test.s3.eu-central-1.amazonaws.com/file.txt', actual: $storage->url(path: 'file.txt'));
    }

    public function test_s3_storage_keeps_virtual_objects() : void
    {
        $storage = new S3StorageAdapter(config: ['bucket' => 'avax-test']);

        $storage->put(path: 'object.txt', contents: 'payload');

        self::assertSame(expected: 'payload', actual: $storage->get(path: 'object.txt'));
    }

    public function test_storage_facade_accepts_named_disks() : void
    {
        $storage = $this->localStorage();
        Storage::useDisk(name: 'unit', adapter: $storage);

        Storage::disk(name: 'unit')->put(path: 'facade.txt', contents: 'ok');

        self::assertSame(expected: 'ok', actual: Storage::disk(name: 'unit')->get(path: 'facade.txt'));
    }
}

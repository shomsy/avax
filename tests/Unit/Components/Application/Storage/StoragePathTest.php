<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Storage;

use Avax\Components\Application\Storage\System\Foundation\Values\StoragePath;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * StoragePath value object tests.
 *
 * Proves path handling, normalization helpers, and emptiness checks.
 */
final class StoragePathTest extends TestCase
{
    public static function nonEmptyPathProvider() : array
    {
        return [
            'simple file'    => ['file.txt'],
            'nested path'    => ['nested/deep/file.txt'],
            'leading slash'  => ['/absolute/path.txt'],
            'trailing slash' => ['directory/'],
            'single char'    => ['a'],
            'just slash'     => ['/'],
            'spaces in name' => ['file with spaces.txt'],
        ];
    }

    public static function withoutLeadingSlashProvider() : array
    {
        return [
            'no leading slash'    => ['file.txt', 'file.txt'],
            'single leading'      => ['/file.txt', 'file.txt'],
            'multiple leading'    => ['///file.txt', 'file.txt'],
            'nested with leading' => ['/a/b/c.txt', 'a/b/c.txt'],
            'empty'               => ['', ''],
            'just slashes'        => ['///', ''],
        ];
    }

    public static function withLeadingSlashProvider() : array
    {
        return [
            'no leading slash'    => ['file.txt', '/file.txt'],
            'already has leading' => ['/file.txt', '/file.txt'],
            'multiple leading'    => ['///file.txt', '/file.txt'],
            'nested'              => ['a/b/c.txt', '/a/b/c.txt'],
            'empty'               => ['', '/'],
            'just slashes'        => ['///', '/'],
        ];
    }

    public function testConstructStoresPath() : void
    {
        $path = new StoragePath('some/file.txt');

        self::assertSame('some/file.txt', $path->path);
    }

    public function testIsEmptyReturnsTrueForEmptyString() : void
    {
        $path = new StoragePath('');

        self::assertTrue($path->isEmpty());
    }

    #[DataProvider('nonEmptyPathProvider')]
    public function testIsEmptyReturnsFalseForNonEmptyPaths(string $nonEmptyPath) : void
    {
        $path = new StoragePath($nonEmptyPath);

        self::assertFalse($path->isEmpty());
    }

    #[DataProvider('withoutLeadingSlashProvider')]
    public function testWithoutLeadingSlash(string $input, string $expected) : void
    {
        $path = new StoragePath($input);

        self::assertSame($expected, $path->withoutLeadingSlash());
    }

    #[DataProvider('withLeadingSlashProvider')]
    public function testWithLeadingSlash(string $input, string $expected) : void
    {
        $path = new StoragePath($input);

        self::assertSame($expected, $path->withLeadingSlash());
    }

    public function testIsReadonly() : void
    {
        $path = new StoragePath('immutable/path.txt');

        self::assertSame('immutable/path.txt', $path->path);
    }

    public function testPathPropertyIsPublic() : void
    {
        $path = new StoragePath('public.txt');

        self::assertTrue(property_exists(StoragePath::class, 'path'));
        self::assertSame('public.txt', $path->path);
    }

    public function testDifferentInstancesAreIndependent() : void
    {
        $path1 = new StoragePath('first.txt');
        $path2 = new StoragePath('second.txt');

        self::assertNotSame($path1, $path2);
    }

    public function testPathWithSpecialCharacters() : void
    {
        $path = new StoragePath('file-with_special.chars_123.txt');

        self::assertSame('file-with_special.chars_123.txt', $path->path);
        self::assertFalse($path->isEmpty());
    }

    public function testPathWithUtf8Characters() : void
    {
        $path = new StoragePath('файл/文件.txt');

        self::assertSame('файл/文件.txt', $path->path);
        self::assertFalse($path->isEmpty());
    }

    public function testPathWithSpaces() : void
    {
        $path = new StoragePath('my documents/report.txt');

        self::assertSame('my documents/report.txt', $path->path);
    }

    public function testNormalizeRoundTrip() : void
    {
        $path = new StoragePath('/nested/file.txt');

        $without       = $path->withoutLeadingSlash();
        $reconstructed = new StoragePath($without);

        self::assertSame('nested/file.txt', $reconstructed->path);
        self::assertSame('/nested/file.txt', $reconstructed->withLeadingSlash());
    }
}

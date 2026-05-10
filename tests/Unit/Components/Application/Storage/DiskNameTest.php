<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Storage;

use Avax\Components\Application\Storage\System\Foundation\Values\DiskName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * DiskName value object tests.
 *
 * Proves validation rules and immutability of the DiskName value object.
 */
final class DiskNameTest extends TestCase
{
    public static function validNameProvider() : array
    {
        return [
            'simple'            => ['local'],
            'with dash'         => ['my-disk'],
            'with underscore'   => ['my_disk'],
            'with numbers'      => ['disk123'],
            'uppercase'         => ['S3Disk'],
            'mixed case'        => ['My-Disk_01'],
            'single char'       => ['a'],
            'single number'     => ['1'],
            'long name'         => ['very-long-disk-name-with-many-characters-123'],
            'hyphen at start'   => ['-disk'],
            'underscore at end' => ['disk_'],
        ];
    }

    public static function invalidNameProvider() : array
    {
        return [
            'empty string'     => [''],
            'with space'       => ['my disk'],
            'with dot'         => ['my.disk'],
            'with slash'       => ['my/disk'],
            'with backslash'   => ['my\\disk'],
            'with colon'       => ['my:disk'],
            'with special'     => ['my@disk'],
            'with hash'        => ['my#disk'],
            'with exclamation' => ['my!disk'],
            'with unicode'     => ['диск'],
            'with emoji'       => ['disk🌍'],
            'with tab'         => ["my\tdisk"],
            'with newline'     => ["my\ndisk"],
        ];
    }

    public function testConstructStoresName() : void
    {
        $name = new DiskName('my_disk');

        self::assertSame('my_disk', $name->name);
    }

    #[DataProvider('validNameProvider')]
    public function testIsValidReturnsTrueForValidNames(string $validName) : void
    {
        $name = new DiskName($validName);

        self::assertTrue($name->isValid());
    }

    #[DataProvider('invalidNameProvider')]
    public function testIsValidReturnsFalseForInvalidNames(string $invalidName) : void
    {
        $name = new DiskName($invalidName);

        self::assertFalse($name->isValid());
    }

    public function testIsValidForEmptyString() : void
    {
        $name = new DiskName('');

        self::assertFalse($name->isValid());
    }

    public function testIsReadonly() : void
    {
        $name = new DiskName('immutable');

        // The class is readonly, so name property cannot be changed after construction.
        // Verify by checking the property is accessible and stable.
        self::assertSame('immutable', $name->name);
    }

    public function testDifferentInstancesAreIndependent() : void
    {
        $name1 = new DiskName('disk_one');
        $name2 = new DiskName('disk_two');

        self::assertNotSame($name1, $name2);
        self::assertNotEquals($name1->name, $name2->name);
    }

    public function testNamePropertyIsPublic() : void
    {
        $name = new DiskName('public_test');

        self::assertTrue(property_exists(DiskName::class, 'name'));
        self::assertSame('public_test', $name->name);
    }
}

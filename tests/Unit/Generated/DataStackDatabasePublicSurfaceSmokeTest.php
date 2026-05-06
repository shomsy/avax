<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Generated;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DataStackDatabasePublicSurfaceSmokeTest extends TestCase
{
    /**
     * @return iterable<array{0: class-string}>
     */
    public static function publicSurfaceClasses(): iterable
    {
        return [
            ['Avax\Components\DataStack\Database\System\PublicSurface\DatabaseInterface'],
            ['Avax\Components\DataStack\Database\System\PublicSurface\EntityManager'],
        ];
    }

    #[DataProvider('publicSurfaceClasses')]
    public function test_public_surface_class_is_autoloadable(string $class): void
    {
        self::assertTrue(
            condition: class_exists($class) || interface_exists($class) || trait_exists($class) || enum_exists($class),
            message  : $class.' must be autoloadable.'
        );
    }
}

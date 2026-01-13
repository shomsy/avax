<?php

declare(strict_types=1);

use Avax\HTTP\Router\Support\RoutePathValidator;
use PHPUnit\Framework\TestCase;

/**
 * Tests for RoutePathValidator wildcard and optional parameter validation.
 */
class RoutePathValidatorTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider validPathsProvider
     */
    public function validates_valid_paths(string $path) : void
    {
        // Should not throw exception
        RoutePathValidator::validate(path: $path);
        $this->assertTrue(condition: true);
    }

    /**
     * @test
     *
     * @dataProvider invalidPathsProvider
     */
    public function rejects_invalid_paths(string $path, string $expectedMessage) : void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: $expectedMessage);

        RoutePathValidator::validate(path: $path);
    }

    /**
     * @test
     */
    public function rejects_empty_path() : void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: 'Route path cannot be empty');

        RoutePathValidator::validate(path: '');
    }

    /**
     * @test
     */
    public function rejects_path_without_leading_slash() : void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: 'Route path must start with "/"');

        RoutePathValidator::validate(path: 'users/{id}');
    }

    /**
     * @test
     */
    public function rejects_invalid_parameter_names() : void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: 'Invalid parameter name');

        RoutePathValidator::validate(path: '/users/{123invalid}');
    }

    /**
     * @test
     */
    public function rejects_multiple_wildcards() : void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: 'Multiple wildcard parameters found');

        RoutePathValidator::validate(path: '/blog/{slug*}/{id*}');
    }

    /**
     * @test
     */
    public function rejects_wildcard_not_at_end() : void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: 'must be at the end of the path');

        RoutePathValidator::validate(path: '/blog/{slug*}/comments');
    }

    /**
     * @test
     */
    public function rejects_optional_after_wildcard() : void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: 'cannot appear after wildcard');

        RoutePathValidator::validate(path: '/blog/{slug*}/{page?}');
    }

    /**
     * @test
     */
    public function rejects_multiple_optional_modifiers() : void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: 'cannot have multiple ?');

        RoutePathValidator::validate(path: '/blog/{slug??}');
    }

    /**
     * @test
     */
    public function rejects_wildcard_and_optional_combination() : void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: 'cannot combine ? and * modifiers');

        RoutePathValidator::validate(path: '/blog/{slug?*}');
    }

    /**
     * @test
     */
    public function extracts_parameter_names() : void
    {
        $names = RoutePathValidator::extractParameterNames(path: '/users/{id}/posts/{slug}');
        $this->assertEquals(expected: ['id', 'slug'], actual: $names);
    }

    /**
     * @test
     */
    public function extracts_parameter_names_with_modifiers() : void
    {
        $names = RoutePathValidator::extractParameterNames(path: '/blog/{slug?}/posts/{id*}');
        $this->assertEquals(expected: ['slug', 'id'], actual: $names);
    }

    /**
     * @test
     */
    public function detects_wildcard_paths() : void
    {
        $this->assertTrue(condition: RoutePathValidator::hasWildcard(path: '/blog/{slug*}'));
        $this->assertFalse(condition: RoutePathValidator::hasWildcard(path: '/blog/{slug}'));
    }

    /**
     * @test
     */
    public function detects_optional_paths() : void
    {
        $this->assertTrue(condition: RoutePathValidator::hasOptional(path: '/blog/{slug?}'));
        $this->assertFalse(condition: RoutePathValidator::hasOptional(path: '/blog/{slug}'));
    }

    public static function validPathsProvider() : array
    {
        return [
            ['/'],
            ['/users'],
            ['/users/{id}'],
            ['/users/{id}/posts'],
            ['/blog/{slug?}'],
            ['/blog/{year}/{month?}'],
            ['/files/{path*}'],
            ['/api/v1/users/{id}/posts/{slug}'],
        ];
    }

    public static function invalidPathsProvider() : array
    {
        return [
            ['/blog/{slug*}/comments', 'must be at the end of the path'],
            ['/blog/{slug*}/{id*}', 'Multiple wildcard parameters found'],
            ['/blog/{slug*}/{page?}', 'cannot appear after wildcard'],
            ['/users/{123invalid}', 'Invalid parameter name'],
            ['/blog/{slug??}', 'cannot have multiple ?'],
            ['/blog/{slug?*}', 'cannot combine ? and * modifiers'],
        ];
    }
}

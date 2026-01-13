<?php

declare(strict_types=1);

use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\Routing\RouteDefinition;
use Avax\HTTP\Router\Validation\Exceptions\InvalidConstraintException;
use Avax\HTTP\Router\Validation\RouteConstraintValidator;
use PHPUnit\Framework\TestCase;

/**
 * Tests for hardened regex constraint validation.
 *
 * Verifies @ suppression removal and proper exception handling.
 */
class RegexConstraintTest extends TestCase
{
    private RouteConstraintValidator $validator;

    /**
     * @test
     * @throws \Avax\HTTP\Router\Validation\Exceptions\InvalidConstraintException
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function validates_correct_constraints() : void
    {
        $route = new RouteDefinition(
            method     : 'GET',
            path       : '/users/{id}',
            action     : 'UserController@show',
            constraints: ['id' => '\d+']
        );

        $request = $this->createMock(Request::class);
        $request->method('getAttribute')->with('id')->willReturn(value: '123');

        // Should not throw exception
        $this->validator->validate(route: $route, request: $request);
        $this->assertTrue(condition: true);
    }

    /**
     * @test
     * @throws \Avax\HTTP\Router\Validation\Exceptions\InvalidConstraintException
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function rejects_constraint_validation_failure() : void
    {
        $route = new RouteDefinition(
            method     : 'GET',
            path       : '/users/{id}',
            action     : 'UserController@show',
            constraints: ['id' => '\d+']
        );

        $request = $this->createMock(Request::class);
        $request->method('getAttribute')->with('id')->willReturn(value: 'abc');

        $this->expectException(exception: RuntimeException::class);
        $this->expectExceptionMessage(message: 'Route parameter "id" failed constraint');

        $this->validator->validate(route: $route, request: $request);
    }

    /**
     * @test
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function rejects_invalid_regex_patterns() : void
    {
        $route = new RouteDefinition(
            method     : 'GET',
            path       : '/users/{id}',
            action     : 'UserController@show',
            constraints: ['id' => '[invalid'] // Missing closing bracket
        );

        $request = $this->createMock(Request::class);

        $this->expectException(exception: InvalidConstraintException::class);
        $this->expectExceptionMessage(message: 'Invalid route constraint pattern');

        $this->validator->validate(route: $route, request: $request);
    }

    /**
     * @test
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function handles_regex_compilation_errors() : void
    {
        $route = new RouteDefinition(
            method     : 'GET',
            path       : '/users/{id}',
            action     : 'UserController@show',
            constraints: ['id' => '(unclosed'] // Unclosed parenthesis
        );

        $request = $this->createMock(Request::class);
        $request->method('getAttribute')->with('id')->willReturn(value: 'test');

        $this->expectException(exception: InvalidConstraintException::class);
        $this->expectExceptionMessage(message: 'regex compilation failed');

        $this->validator->validate(route: $route, request: $request);
    }

    /**
     * @test
     * @throws \Avax\HTTP\Router\Validation\Exceptions\InvalidConstraintException
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function escapes_special_regex_characters() : void
    {
        // Test that delimiters in patterns are properly escaped
        $route = new RouteDefinition(
            method     : 'GET',
            path       : '/files/{path}',
            action     : 'FileController@show',
            constraints: ['path' => 'folder/subfolder/file.txt'] // Contains slashes
        );

        $request = $this->createMock(Request::class);
        $request->method('getAttribute')->with('path')->willReturn(value: 'folder/subfolder/file.txt');

        // Should work because slashes are escaped
        $this->validator->validate(route: $route, request: $request);
        $this->assertTrue(condition: true);
    }

    /**
     * @test
     * @throws \Avax\HTTP\Router\Validation\Exceptions\InvalidConstraintException
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function skips_non_string_non_numeric_attributes() : void
    {
        $route = new RouteDefinition(
            method     : 'GET',
            path       : '/users/{id}',
            action     : 'UserController@show',
            constraints: ['id' => '\d+']
        );

        $request = $this->createMock(Request::class);
        $request->method('getAttribute')->with('id')->willReturn(value: ['array']); // Array, not string/numeric

        // Should skip validation for non-string/non-numeric values
        $this->validator->validate(route: $route, request: $request);
        $this->assertTrue(condition: true);
    }

    /**
     * @test
     * @throws \Avax\HTTP\Router\Validation\Exceptions\InvalidConstraintException
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function validates_multiple_constraints() : void
    {
        $route = new RouteDefinition(
            method     : 'GET',
            path       : '/users/{id}/posts/{slug}',
            action     : 'PostController@show',
            constraints: [
                'id'   => '\d+',
                'slug' => '[a-z0-9-]+',
            ]
        );

        $request = $this->createMock(Request::class);
        $request->method('getAttribute')
            ->willReturnCallback(callback: static function ($attr) {
                return match ($attr) {
                    'id'    => '123',
                    'slug'  => 'hello-world',
                    default => null
                };
            });

        // Should validate all constraints successfully
        $this->validator->validate(route: $route, request: $request);
        $this->assertTrue(condition: true);
    }

    /**
     * @test
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function fails_on_first_invalid_constraint() : void
    {
        $route = new RouteDefinition(
            method     : 'GET',
            path       : '/users/{id}/posts/{slug}',
            action     : 'PostController@show',
            constraints: [
                'id'   => '[invalid', // Invalid pattern
                'slug' => '[a-z0-9-]+',
            ]
        );

        $request = $this->createMock(Request::class);

        $this->expectException(exception: InvalidConstraintException::class);

        $this->validator->validate(route: $route, request: $request);
    }

    protected function setUp() : void
    {
        $this->validator = new RouteConstraintValidator;
    }
}

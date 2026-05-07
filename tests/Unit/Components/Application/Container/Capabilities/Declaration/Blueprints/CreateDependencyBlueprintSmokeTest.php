<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Container\Capabilities\Declaration\Blueprints;

use Avax\Components\Application\Container\System\Capabilities\Declaration\Blueprints\BlueprintCache;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Blueprints\CreateDependencyBlueprint;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Blueprints\DependencyBlueprint;
use Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Attributes\Inject;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependencies;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolvePlan;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\Lifetimes\Attributes\Singleton;
use Avax\Tests\TestCase;
use DateTimeImmutable;
use stdClass;

final class CreateDependencyBlueprintSmokeTest extends TestCase
{
    private string $cacheDir;

    public function test_blueprint_generation_and_caching() : void
    {
        $version = 'blueprint-smoke';
        $factory = new CreateDependencyBlueprint(
            blueprintCache     : new BlueprintCache(cacheDir: $this->cacheDir, cacheVersion: $version),
            resolveDependencies: new ResolveDependencies(),
        );

        $first  = $factory->createFor(class: BlueprintTargetStub::class);
        $second = $factory->createFor(class: BlueprintTargetStub::class);

        $reloadedFactory = new CreateDependencyBlueprint(
            blueprintCache     : new BlueprintCache(cacheDir: $this->cacheDir, cacheVersion: $version),
            resolveDependencies: new ResolveDependencies(),
        );
        $reloaded        = $reloadedFactory->createFor(class: BlueprintTargetStub::class);

        $this->assertTrue(condition: $first->shared, message: 'Singleton attribute should mark a blueprint as shared.');
        $this->assertTrue(condition: $first->instantiable, message: 'Blueprint should mark instantiable classes.');
        $this->assertInstanceOf(expected: ResolvePlan::class, actual: $first->constructor, message: 'Blueprint should compile a constructor resolve plan.');
        $this->assertCount(expectedCount: 1, haystack: $first->injectableProperties, message: 'Blueprint should collect injectable properties.');
        $this->assertCount(expectedCount: 1, haystack: $first->injectableMethods, message: 'Blueprint should collect injectable methods.');
        $this->assertSame(expected: $first, actual: $second, message: 'Blueprint cache should return the same blueprint instance.');

        // Note: assertNotSame because it's a new factory instance loading from disk
        $this->assertNotSame(expected: $first, actual: $reloaded, message: 'A new factory should load a fresh blueprint instance from compiled cache.');

        $this->assertSame(expected: 'property', actual: $first->injectableProperties[0]['name']);
        $this->assertSame(expected: stdClass::class, actual: $first->injectableProperties[0]['serviceId']);
        $this->assertSame(expected: 'wire', actual: $first->injectableMethods[0]['name']);
        $this->assertInstanceOf(expected: ResolvePlan::class, actual: $first->injectableMethods[0]['plan']);

        $cachePath = $this->cacheDir . '/container/' . rawurlencode(string: $version) . '/blueprints/' . sha1(string: BlueprintTargetStub::class) . '.php';
        $this->assertFileExists(filename: $cachePath, message: 'Blueprint cache should write a compiled artifact to disk.');
    }

    protected function setUp() : void
    {
        parent::setUp();
        $this->cacheDir = sys_get_temp_dir() . '/container-blueprint-' . uniqid();
    }

    protected function tearDown() : void
    {
        if (is_dir($this->cacheDir)) {
            $this->recursiveDelete($this->cacheDir);
        }
        parent::tearDown();
    }

    private function recursiveDelete(string $dir) : void
    {
        if (! is_dir($dir)) {
            return;
        }

        foreach (glob($dir . '/*') as $file) {
            is_dir($file) ? $this->recursiveDelete($file) : unlink($file);
        }

        rmdir($dir);
    }
}

/**
 * Stub class for blueprint testing.
 */
#[Singleton]
final class BlueprintTargetStub
{
    #[Inject]
    public stdClass $property;

    public DateTimeImmutable $createdAt;

    public function __construct(DateTimeImmutable $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    #[Inject]
    protected function wire(DateTimeImmutable $clock) : void {}
}

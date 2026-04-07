<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

use Avax\Container\DependencyInjection\Injection\Attributes\Inject;
use Avax\Container\DependencyInjection\Dependencies\Blueprints\BlueprintCache;
use Avax\Container\DependencyInjection\Dependencies\Blueprints\CreateServiceBlueprint;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ResolveDependencies;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ResolvePlan;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\Attributes\Singleton;

#[Singleton]
final class BlueprintTarget
{
    public function __construct(public DateTimeImmutable $createdAt)
    {
    }

    #[Inject]
    public stdClass $property;

    #[Inject]
    protected function wire(DateTimeImmutable $clock) : void
    {
    }
}

$cacheDir = sys_get_temp_dir() . '/container-blueprint-' . uniqid();
$version = 'blueprint-smoke';
$factory = new CreateServiceBlueprint(
    new BlueprintCache(cacheDir: $cacheDir, cacheVersion: $version),
    new ResolveDependencies()
);
$first = $factory->createFor(BlueprintTarget::class);
$second = $factory->createFor(BlueprintTarget::class);
$reloaded = (new CreateServiceBlueprint(
    new BlueprintCache(cacheDir: $cacheDir, cacheVersion: $version),
    new ResolveDependencies()
))->createFor(BlueprintTarget::class);

assertTrue($first->shared, 'Singleton attribute should mark a blueprint as shared.');
assertTrue($first->instantiable, 'Blueprint should mark instantiable classes.');
assertInstanceOf(ResolvePlan::class, $first->constructor, 'Blueprint should compile a constructor resolve plan.');
assertSame(1, count($first->injectableProperties), 'Blueprint should collect injectable properties.');
assertSame(1, count($first->injectableMethods), 'Blueprint should collect injectable methods.');
assertSame($first, $second, 'Blueprint cache should return the same blueprint instance.');
assertNotSame($first, $reloaded, 'A new factory should load a fresh blueprint instance from compiled cache.');
assertSame('property', $first->injectableProperties[0]['name'], 'Compiled property metadata should keep the property name.');
assertSame(stdClass::class, $first->injectableProperties[0]['serviceId'], 'Compiled property metadata should keep the dependency id.');
assertSame('wire', $first->injectableMethods[0]['name'], 'Compiled method metadata should keep the method name.');
assertInstanceOf(ResolvePlan::class, $first->injectableMethods[0]['plan'], 'Compiled method metadata should keep a resolve plan.');
assertTrue(is_file($cacheDir . '/container/' . rawurlencode($version) . '/blueprints/' . sha1(BlueprintTarget::class) . '.php'), 'Blueprint cache should write a compiled artifact to disk.');

echo basename(__FILE__) . " ok\n";

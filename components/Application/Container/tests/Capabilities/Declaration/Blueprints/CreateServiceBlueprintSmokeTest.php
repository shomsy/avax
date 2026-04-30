<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, levels: 3) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Declaration\Blueprints\BlueprintCache;
use Avax\Components\Application\Container\DI\Capabilities\Declaration\Blueprints\CreateServiceBlueprint;
use Avax\Components\Application\Container\DI\Capabilities\Execution\Injection\Attributes\Inject;
use Avax\Components\Application\Container\DI\Capabilities\Resolution\ResolveDependencies;
use Avax\Components\Application\Container\DI\Capabilities\Resolution\ResolvePlan;
use Avax\Components\Application\Container\DI\Capabilities\Runtime\Scopes\Lifetimes\Attributes\Singleton;

#[Singleton]
final class CreateServiceBlueprintSmokeTest
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

$cacheDir = sys_get_temp_dir() . '/container-blueprint-' . uniqid();
$version  = 'blueprint-smoke';
$factory  = new CreateServiceBlueprint(
    cache       : new BlueprintCache(cacheDir: $cacheDir, cacheVersion: $version),
    dependencies: new ResolveDependencies(),
);
$first    = $factory->createFor(class: BlueprintTarget::class);
$second   = $factory->createFor(class: BlueprintTarget::class);
$reloaded = new CreateServiceBlueprint(
    cache       : new BlueprintCache(cacheDir: $cacheDir, cacheVersion: $version),
    dependencies: new ResolveDependencies(),
)->createFor(class: BlueprintTarget::class);

assertTrue(condition: $first->shared, message: 'Singleton attribute should mark a blueprint as shared.');
assertTrue(condition: $first->instantiable, message: 'Blueprint should mark instantiable classes.');
assertInstanceOf(expectedClass: ResolvePlan::class, value: $first->constructor, message: 'Blueprint should compile a constructor resolve plan.');
assertSame(expected: 1, actual: count(value: $first->injectableProperties), message: 'Blueprint should collect injectable properties.');
assertSame(expected: 1, actual: count(value: $first->injectableMethods), message: 'Blueprint should collect injectable methods.');
assertSame(expected: $first, actual: $second, message: 'Blueprint cache should return the same blueprint instance.');
assertNotSame(expected: $first, actual: $reloaded, message: 'A new factory should load a fresh blueprint instance from compiled cache.');
assertSame(expected: 'property', actual: $first->injectableProperties[0]['name'], message: 'Compiled property metadata should keep the property name.');
assertSame(expected: stdClass::class, actual: $first->injectableProperties[0]['serviceId'], message: 'Compiled property metadata should keep the dependency id.');
assertSame(expected: 'wire', actual: $first->injectableMethods[0]['name'], message: 'Compiled method metadata should keep the method name.');
assertInstanceOf(expectedClass: ResolvePlan::class, value: $first->injectableMethods[0]['plan'], message: 'Compiled method metadata should keep a resolve plan.');
assertTrue(condition: is_file(filename: $cacheDir . '/container/' . rawurlencode(string: $version) . '/blueprints/' . sha1(string: BlueprintTarget::class) . '.php'), message: 'Blueprint cache should write a compiled artifact to disk.');

echo basename(path: __FILE__) . " ok\n";

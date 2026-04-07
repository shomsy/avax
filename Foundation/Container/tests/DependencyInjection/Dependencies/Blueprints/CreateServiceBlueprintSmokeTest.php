<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

use Avax\Container\DependencyInjection\Injection\Attributes\Inject;
use Avax\Container\DependencyInjection\Dependencies\Blueprints\BlueprintCache;
use Avax\Container\DependencyInjection\Dependencies\Blueprints\CreateServiceBlueprint;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\Attributes\Singleton;

#[Singleton]
final class BlueprintTarget
{
    #[Inject]
    public stdClass $property;

    #[Inject]
    protected function wire(DateTimeImmutable $clock) : void
    {
    }
}

$factory = new CreateServiceBlueprint(new BlueprintCache());
$first = $factory->createFor(BlueprintTarget::class);
$second = $factory->createFor(BlueprintTarget::class);

assertTrue($first->shared, 'Singleton attribute should mark a blueprint as shared.');
assertSame(1, count($first->injectableProperties), 'Blueprint should collect injectable properties.');
assertSame(1, count($first->injectableMethods), 'Blueprint should collect injectable methods.');
assertSame($first, $second, 'Blueprint cache should return the same blueprint instance.');

echo basename(__FILE__) . " ok\n";

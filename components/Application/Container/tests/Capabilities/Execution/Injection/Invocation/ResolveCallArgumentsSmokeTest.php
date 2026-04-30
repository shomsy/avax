<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, 4) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Execution\Injection\Invocation\ResolveCallArguments;
use Avax\Components\Application\Container\DI\Capabilities\Resolution\ResolveDependencies;
use Avax\Components\Application\Container\DI\Capabilities\Resolution\ResolveDependency;

interface CallArgumentGreeterContract
{
    public function message() : string;
}

final class CallArgumentGreeter implements CallArgumentGreeterContract
{
    #[Override]
    public function message() : string
    {
        return 'args';
    }
}

final class CallArgumentTarget
{
    public function handle(CallArgumentGreeterContract $callArgumentGreeterContract, string $name = 'fallback') : array
    {
        return [$callArgumentGreeterContract->message(), $name];
    }
}

$container = makeTestContainer();
$container->bind(abstract: CallArgumentGreeterContract::class, concrete: CallArgumentGreeter::class);

$resolver = $container->get(id: ResolveDependency::class);
$arguments  = new ResolveCallArguments(dependencies: new ResolveDependencies());
$reflection = new ReflectionMethod(objectOrMethod: CallArgumentTarget::class, method: 'handle');
$resolved   = $arguments->resolve(
    parameters: $reflection->getParameters(),
    overrides : ['name' => 'custom'],
    resolver  : $resolver,
);

assertSame(expected: 'args', actual: $resolved[0]->message(), message: 'Call arguments should resolve container-backed dependencies.');
assertSame(expected: 'custom', actual: $resolved[1], message: 'Call arguments should honor explicit overrides.');

echo basename(path: __FILE__) . " ok\n";

<?php

declare(strict_types=1);

require_once dirname(__DIR__, 4) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Execution\Injection\Invocation\ResolveCallArguments;
use Avax\Container\DI\Capabilities\Resolution\ResolveDependencies;
use Avax\Container\DI\Capabilities\Resolution\ServiceResolver;

interface CallArgumentGreeterContract
{
    public function message() : string;
}

final class CallArgumentGreeter implements CallArgumentGreeterContract
{
    public function message() : string
    {
        return 'args';
    }
}

final class CallArgumentTarget
{
    public function handle(CallArgumentGreeterContract $greeter, string $name = 'fallback') : array
    {
        return [$greeter->message(), $name];
    }
}

$container = makeTestContainer();
$container->bind(CallArgumentGreeterContract::class, CallArgumentGreeter::class);

$resolver = $container->get(ServiceResolver::class);
$arguments = new ResolveCallArguments(new ResolveDependencies());
$reflection = new ReflectionMethod(CallArgumentTarget::class, 'handle');
$resolved = $arguments->resolve(
    parameters: $reflection->getParameters(),
    overrides: ['name' => 'custom'],
    resolver: $resolver
);

assertSame('args', $resolved[0]->message(), 'Call arguments should resolve container-backed dependencies.');
assertSame('custom', $resolved[1], 'Call arguments should honor explicit overrides.');

echo basename(__FILE__) . " ok\n";

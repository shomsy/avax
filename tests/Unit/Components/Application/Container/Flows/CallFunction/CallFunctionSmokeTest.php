<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

interface CallGreeterContract
{
    public function message() : string;
}

final class CallFunctionSmokeTest implements CallGreeterContract
{
    #[Override]
    public function message() : string
    {
        return 'hello';
    }
}

final readonly class CallAction
{
    public function __construct(private CallGreeterContract $callGreeterContract) {}

    public static function staticHello() : string
    {
        return 'static';
    }

    public function __invoke() : string
    {
        return $this->callGreeterContract->message();
    }

    public function greet(CallGreeterContract $callGreeterContract) : string
    {
        return $callGreeterContract->message();
    }
}

$container = makeTestContainer();
$container->bind(abstract: CallGreeterContract::class, concrete: CallGreeter::class);

assertSame(expected: 'hello', actual: $container->call(callable: CallAction::class), message: 'Invokable class strings should resolve through the container.');
assertSame(expected: 'hello', actual: $container->call(callable: CallAction::class . '@greet'), message: 'Class@method calls should receive injected arguments.');
assertSame(expected: 'static', actual: $container->call(callable: [CallAction::class, 'staticHello']), message: 'Static callables should still work.');

echo basename(path: __FILE__) . " ok\n";

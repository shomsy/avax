<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, levels: 2) . '/bootstrap.php';

interface CallGreeterContract
{
    public function message() : string;
}

final class CallGreeter implements CallGreeterContract
{
    public function message() : string
    {
        return 'hello';
    }
}

final class CallAction
{
    private CallGreeterContract $greeter;

    public function __construct(CallGreeterContract $greeter) { $this->greeter = $greeter; }

    public static function staticHello() : string
    {
        return 'static';
    }

    public function __invoke() : string
    {
        return $this->greeter->message();
    }

    public function greet(CallGreeterContract $greeter) : string
    {
        return $greeter->message();
    }
}

$container = makeTestContainer();
$container->bind(abstract: CallGreeterContract::class, concrete: CallGreeter::class);

assertSame(expected: 'hello', actual: $container->call(callable: CallAction::class), message: 'Invokable class strings should resolve through the container.');
assertSame(expected: 'hello', actual: $container->call(callable: CallAction::class . '@greet'), message: 'Class@method calls should receive injected arguments.');
assertSame(expected: 'static', actual: $container->call(callable: [CallAction::class, 'staticHello']), message: 'Static callables should still work.');

echo basename(path: __FILE__) . " ok\n";

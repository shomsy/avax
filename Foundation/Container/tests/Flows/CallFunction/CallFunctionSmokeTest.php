<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

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
    public function __construct(private CallGreeterContract $greeter) {}

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
$container->bind(CallGreeterContract::class, CallGreeter::class);

assertSame('hello', $container->call(CallAction::class), 'Invokable class strings should resolve through the container.');
assertSame('hello', $container->call(CallAction::class . '@greet'), 'Class@method calls should receive injected arguments.');
assertSame('static', $container->call([CallAction::class, 'staticHello']), 'Static callables should still work.');

echo basename(__FILE__) . " ok\n";

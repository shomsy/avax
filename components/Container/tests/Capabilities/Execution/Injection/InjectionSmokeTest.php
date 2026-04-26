<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, levels: 3) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Execution\Injection\Attributes\Inject;

interface InjectionGreeterContract
{
    public function message() : string;
}

final class InjectionGreeter implements InjectionGreeterContract
{
    public function message() : string
    {
        return 'injected';
    }
}

final class InjectionTarget
{
    #[Inject]
    public InjectionGreeterContract $greeter;

    public bool $methodInjected = false;

    #[Inject]
    protected function wire(InjectionGreeterContract $greeter) : void
    {
        $this->methodInjected = $greeter->message() === 'injected';
    }
}

$container = makeTestContainer();
$container->bind(abstract: InjectionGreeterContract::class, concrete: InjectionGreeter::class);

$target = $container->injectInto(target: new InjectionTarget());
$report = $container->inspectInjection(target: $target);

assertSame(expected: 'injected', actual: $target->greeter->message(), message: 'Property injection should resolve bound services.');
assertTrue(condition: $target->methodInjected, message: 'Method injection should run after property injection.');
assertTrue(condition: $container->canInject(target: $target), message: 'Container should report injectable targets.');
assertTrue(condition: $report->success, message: 'Injection report should describe injectable targets.');

echo basename(path: __FILE__) . " ok\n";

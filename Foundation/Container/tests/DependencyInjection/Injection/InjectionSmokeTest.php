<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DependencyInjection\Injection\Attributes\Inject;

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
$container->bind(InjectionGreeterContract::class, InjectionGreeter::class);

$target = $container->injectInto(new InjectionTarget());
$report = $container->inspectInjection($target);

assertSame('injected', $target->greeter->message(), 'Property injection should resolve bound services.');
assertTrue($target->methodInjected, 'Method injection should run after property injection.');
assertTrue($container->canInject($target), 'Container should report injectable targets.');
assertTrue($report->success, 'Injection report should describe injectable targets.');

echo basename(__FILE__) . " ok\n";

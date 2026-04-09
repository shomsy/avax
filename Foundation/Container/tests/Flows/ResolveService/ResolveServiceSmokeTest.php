<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

final class ResolveGreeter
{
    public function message() : string
    {
        return 'resolved';
    }
}

final class NeedsResolveGreeter
{
    public function __construct(public ResolveGreeter $greeter) {}
}

final class ResolveWithParameters
{
    public function __construct(public string $name) {}
}

$container = makeTestContainer();

$autowired = $container->get(NeedsResolveGreeter::class);
$built     = $container->make(ResolveWithParameters::class, ['name' => 'custom']);

assertSame('resolved', $autowired->greeter->message(), 'ResolveService should autowire instantiable classes.');
assertSame('custom', $built->name, 'ResolveService should honor explicit make() overrides.');

echo basename(__FILE__) . " ok\n";

<?php

declare(strict_types=1);

require_once dirname(2, path: __DIR__) . '/bootstrap.php';

final class ResolveGreeter
{
    public function message() : string
    {
        return 'resolved';
    }
}

final class NeedsResolveGreeter
{
    public function __construct(public ResolveGreeter $resolveGreeter) {}
}

final class ResolveWithParameters
{
    public function __construct(public string $name) {}
}

$container = makeTestContainer();

$autowired = $container->get(id: NeedsResolveGreeter::class);
$built = $container->make(abstract: ResolveWithParameters::class, parameters: ['name' => 'custom']);

assertSame(expected: 'resolved', actual: $autowired->greeter->message(), message: 'ResolveService should autowire instantiable classes.');
assertSame(expected: 'custom', actual: $built->name, message: 'ResolveService should honor explicit make() overrides.');

echo basename(path: __FILE__) . " ok\n";

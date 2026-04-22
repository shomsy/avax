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
    public ResolveGreeter $greeter;

    public function __construct(ResolveGreeter $greeter) { $this->greeter = $greeter; }
}

final class ResolveWithParameters
{
    public string $name;

    public function __construct(string $name) { $this->name = $name; }
}

$container = makeTestContainer();

$autowired = $container->get(id: NeedsResolveGreeter::class);
$built     = $container->make(abstract: ResolveWithParameters::class, parameters: ['name' => 'custom']);

assertSame(expected: 'resolved', actual: $autowired->greeter->message(), message: 'ResolveService should autowire instantiable classes.');
assertSame(expected: 'custom', actual: $built->name, message: 'ResolveService should honor explicit make() overrides.');

echo basename(__FILE__) . " ok\n";

<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DependencyInjection\Errors\ServiceNotFoundException;

interface CreateGreeterContract
{
    public function message() : string;
}

final class CreateGreeter implements CreateGreeterContract
{
    public function message() : string
    {
        return 'hi';
    }
}

final class NeedsCreateGreeter
{
    public function __construct(public CreateGreeterContract $greeter)
    {
    }
}

$container = makeTestContainer();
$container->bind(CreateGreeterContract::class, CreateGreeter::class);

$resolved = $container->get(NeedsCreateGreeter::class);
assertInstanceOf(NeedsCreateGreeter::class, $resolved, 'CreateContainer should support autowiring.');
assertSame('hi', $resolved->greeter->message(), 'Bound dependency should be injected.');
assertThrows(
    ServiceNotFoundException::class,
    static fn() => $container->get('Missing\\Service'),
    'Missing services must use the not-found contract.'
);

echo basename(__FILE__) . " ok\n";

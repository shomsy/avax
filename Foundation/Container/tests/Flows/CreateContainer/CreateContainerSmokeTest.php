<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Diagnostics\Errors\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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
    public function __construct(public CreateGreeterContract $greeter) {}
}

$container = makeTestContainer();
$container->bind(abstract: CreateGreeterContract::class, concrete: CreateGreeter::class);

$resolved = $container->get(id: NeedsCreateGreeter::class);
assertInstanceOf(expectedClass: NeedsCreateGreeter::class, value: $resolved, message: 'CreateContainer should support autowiring.');
assertSame(expected: 'hi', actual: $resolved->greeter->message(), message: 'Bound dependency should be injected.');
assertThrows(
/**
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */ expectedClass: ServiceNotFoundException::class,
    callback     : static fn () => $container->get(id: 'Missing\\Service'),
    message      : 'Missing services must use the not-found contract.'
);

echo basename(__FILE__) . " ok\n";

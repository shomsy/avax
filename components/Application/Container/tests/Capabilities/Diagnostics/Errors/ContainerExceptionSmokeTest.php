<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, levels: 3) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Components\Application\Container\DI\Capabilities\Diagnostics\Errors\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

$containerException = new ContainerException(message: 'boom');
$notFoundException  = new ServiceNotFoundException(message: 'missing');

assertInstanceOf(expectedClass: ContainerExceptionInterface::class, value: $containerException, message: 'ContainerException should satisfy PSR.');
assertInstanceOf(expectedClass: NotFoundExceptionInterface::class, value: $notFoundException, message: 'ServiceNotFoundException should satisfy PSR.');

echo basename(path: __FILE__) . " ok\n";

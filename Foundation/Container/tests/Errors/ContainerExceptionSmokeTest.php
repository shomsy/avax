<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use Avax\Container\Errors\ContainerException;
use Avax\Container\Errors\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

$containerException = new ContainerException('boom');
$notFoundException = new ServiceNotFoundException('missing');

assertInstanceOf(ContainerExceptionInterface::class, $containerException, 'ContainerException should satisfy PSR.');
assertInstanceOf(NotFoundExceptionInterface::class, $notFoundException, 'ServiceNotFoundException should satisfy PSR.');

echo basename(__FILE__) . " ok\n";

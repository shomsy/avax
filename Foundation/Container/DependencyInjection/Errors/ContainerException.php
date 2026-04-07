<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Errors;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class ContainerException extends RuntimeException implements ContainerExceptionInterface
{
}

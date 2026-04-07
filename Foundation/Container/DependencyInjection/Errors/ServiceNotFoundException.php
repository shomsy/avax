<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Errors;

use Psr\Container\NotFoundExceptionInterface;

final class ServiceNotFoundException extends ContainerException implements NotFoundExceptionInterface
{
}

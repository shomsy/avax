<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\ContainerObservability\Errors;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Explicit PSR-11 not-found boundary for missing services.
 */
final class DependencyNotFoundException extends ContainerException implements NotFoundExceptionInterface
{
}

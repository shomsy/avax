<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Psr\Container\ContainerInterface;
use RuntimeException;

final readonly class RouteFacadeContainer implements ContainerInterface
{
    /**
     * @param array<string, mixed> $services
     */
    public function __construct(private array $services = [])
    {
    }

    public function get(string $id) : mixed
    {
        if (! $this->has(id: $id)) {
            throw new RuntimeException(
                message: sprintf('Service "%s" is not available in the route facade container.', $id),
            );
        }

        return $this->services[$id];
    }

    public function has(string $id) : bool
    {
        return array_key_exists(key: $id, array: $this->services);
    }
}

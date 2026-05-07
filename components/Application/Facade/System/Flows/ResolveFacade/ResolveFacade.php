<?php

declare(strict_types=1);

namespace Avax\Components\Application\Facade\System\Flows\ResolveFacade;

use RuntimeException;

final readonly class ResolveFacade
{
    /**
     * @param array<string, object> $registry
     */
    public function resolve(string $name, array $registry) : object
    {
        if (! isset($registry[$name])) {
            throw new RuntimeException("Facade not found: {$name}");
        }

        return $registry[$name];
    }
}

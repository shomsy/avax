<?php

declare(strict_types=1);

namespace Avax\Components\Application\Facade\System\Flows\RegisterFacade;

final readonly class RegisterFacade
{
    /**
     * @param array<string, object> $registry
     *
     * @return array<string, object>
     */
    public function register(string $name, object $instance, array $registry) : array
    {
        $registry[$name] = $instance;

        return $registry;
    }
}

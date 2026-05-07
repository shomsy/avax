<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Delivery\System\Flows\CompileRoutes;

final readonly class CompileRoutes
{
    /**
     * @param array<string, mixed> $routes
     *
     * @return array{compiled: bool, count: int}
     */
    public function compile(array $routes) : array
    {
        return [
            'compiled' => true,
            'count'    => count($routes),
        ];
    }
}

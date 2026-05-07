<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Delivery\System\Flows\CompileContainer;

final readonly class CompileContainer
{
    /**
     * @param array<string, mixed> $definitions
     *
     * @return array{compiled: bool, definitions: int}
     */
    public function compile(array $definitions) : array
    {
        return [
            'compiled'    => true,
            'definitions' => count($definitions),
        ];
    }
}

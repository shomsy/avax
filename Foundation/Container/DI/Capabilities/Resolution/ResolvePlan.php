<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Resolution;

/**
 * Compiled parameter resolution plan.
 */
final readonly class ResolvePlan
{
    public array $parameters;

    /**
     * @param list<array{
     *     name: string,
     *     serviceId: string|null,
     *     source: string,
     *     inputName: string,
     *     hasDefault: bool,
     *     default: string,
     *     allowsNull: bool
     * }> $parameters
     */
    public function __construct(
        array $parameters = []
    )
    {
        $this->parameters = $parameters;
    }

    public static function __set_state(array $state) : self
    {
        return new self(parameters: $state['parameters'] ?? []);
    }

    public function isEmpty() : bool
    {
        return $this->parameters === [];
    }
}

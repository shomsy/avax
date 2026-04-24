<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Flows\Pipeline;

use Avax\DataFoundation\Exceptions\InvalidFlowException;

/**
 * Immutable ordered pipeline of callables.
 */
final readonly class Pipeline
{
    /**
     * @param array<int, Pipe> $pipes
     */
    public function __construct(
        private array $pipes = [],
    ) {}

    public function pipe(callable $callback, ?string $name = null) : self
    {
        $pipes   = $this->pipes;
        $pipes[] = Pipe::from(callback: $callback, name: $name);

        return new self(pipes: $pipes);
    }

    public function process(mixed $input) : mixed
    {
        if ($this->pipes === []) {
            throw InvalidFlowException::pipelineHasNoStages();
        }

        $value = $input;

        foreach ($this->pipes as $pipe) {
            $value = $pipe($value);
        }

        return $value;
    }

    /**
     * @return array<int, string>
     */
    public function stageNames() : array
    {
        return array_map(static fn (Pipe $pipe) : string => $pipe->name(), $this->pipes);
    }
}

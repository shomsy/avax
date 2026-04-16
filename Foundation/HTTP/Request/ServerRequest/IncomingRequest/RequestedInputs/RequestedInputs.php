<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs;

use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\Inputs\DTO\InputsDTO;
use BackedEnum;

/**
 * Capability Owner: Provides a DSL-friendly way to access request inputs.
 *
 * Aggregates Query Parameters and Parsed Body data.
 * Delegates to InputsDTO for strongly-typed operations.
 */
final readonly class RequestedInputs
{
    public function __construct(
        private InputsDTO $dto
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->dto->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function query(): array
    {
        return $this->dto->query;
    }

    /**
     * @return array<string, mixed>
     */
    public function body(): array
    {
        return $this->dto->body;
    }

    /**
     * Map the requested inputs to a DTO class.
     *
     * @template T of AbstractDTO
     * @param class-string<T> $dtoClass The Fully Qualified Class Name of the DTO.
     * @return T
     */
    public function as(string $dtoClass): AbstractDTO
    {
        return $this->dto->as($dtoClass);
    }

    public function bool(string $key, bool $default = false): bool
    {
        return $this->dto->bool($key, $default);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->dto->get($key, $default);
    }

    public function int(string $key, int $default = 0): int
    {
        return $this->dto->int($key, $default);
    }

    public function float(string $key, float $default = 0.0): float
    {
        return $this->dto->float($key, $default);
    }

    public function text(string $key, string $default = ''): string
    {
        return $this->dto->string($key, $default);
    }

    /**
     * @param string $key
     * @param array  $default
     *
     * @return array
     */
    public function list(string $key, array $default = []): array
    {
        return $this->dto->array($key, $default);
    }

    public function has(string $key): bool
    {
        return $this->dto->has($key);
    }

    public function only(array $keys): array
    {
        return $this->dto->only($keys);
    }

    public function except(array $keys): array
    {
        return $this->dto->except($keys);
    }

    /**
     * @template T of BackedEnum
     * @param string $key
     * @param class-string<T> $enumClass
     * @param T|null $default
     * @return T|null
     */
    public function enum(string $key, string $enumClass, mixed $default = null): BackedEnum|null
    {
        return $this->dto->enum($key, $enumClass, $default);
    }
}

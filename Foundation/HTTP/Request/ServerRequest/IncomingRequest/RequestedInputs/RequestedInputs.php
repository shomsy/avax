<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs\InputValue;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs\Inputs;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Mapping\MapRequestedInputsToDto;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Sanitization\InputSanitizer;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Sanitization\SanitizedRequestedInputs;
use BackedEnum;

/**
 * RequestedInputs
 *
 * Capability owner for typed, source-aware, and sanitized input access.
 *
 * Semantic rules:
 * - key presence uses array_key_exists()
 * - null means "present but null"
 * - body wins over query when the same key exists in both
 * - sanitization is exposed through a dedicated sanitized view
 * - DTO creation is delegated to the mapper
 */
final readonly class RequestedInputs
{
    public function __construct(
        private Inputs $inputs,
        private InputSanitizer $sanitizer,
        private MapRequestedInputsToDto $mapper,
    ) {
    }

    /**
     * @param array<string, mixed> $queryParams
     * @param array<string, mixed> $parsedBody
     */
    public static function fromQueryAndBody(
        array $queryParams,
        array $parsedBody,
        InputSanitizer $sanitizer,
        MapRequestedInputsToDto $mapper,
    ): self {
        return new self(
            inputs: Inputs::fromQueryAndBody(
                queryParams: $queryParams,
                parsedBody: $parsedBody,
            ),
            sanitizer: $sanitizer,
            mapper: $mapper,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->inputs->all();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->inputs->get(key: $key, default: $default);
    }

    public function has(string $key): bool
    {
        return $this->inputs->has(key: $key);
    }

    public function hasNonNull(string $key): bool
    {
        return $this->inputs->hasNonNull(key: $key);
    }

    public function string(string $key, string $default = ''): string
    {
        return $this->inputs->string(key: $key, default: $default);
    }

    public function int(string $key, int $default = 0): int
    {
        return $this->inputs->int(key: $key, default: $default);
    }

    public function float(string $key, float $default = 0.0): float
    {
        return $this->inputs->float(key: $key, default: $default);
    }

    public function bool(string $key, bool $default = false): bool
    {
        return $this->inputs->bool(key: $key, default: $default);
    }

    /**
     * @param array<string, mixed> $default
     *
     * @return array<string, mixed>
     */
    public function array(string $key, array $default = []): array
    {
        return $this->inputs->array(key: $key, default: $default);
    }

    /**
     * @template T of BackedEnum
     *
     * @param class-string<T> $enumClass
     *
     * @return T|null
     */
    public function enum(string $key, string $enumClass, mixed $default = null): BackedEnum|null
    {
        return $this->inputs->enum(
            key: $key,
            enumClass: $enumClass,
            default: $default,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function only(string ...$keys): array
    {
        return $this->inputs->only(keys: $keys);
    }

    /**
     * @return array<string, mixed>
     */
    public function except(string ...$keys): array
    {
        return $this->inputs->except(keys: $keys);
    }

    public function value(string $key, mixed $default = null): InputValue
    {
        return $this->inputs->value(key: $key, default: $default);
    }

    public function fromQuery(string $key, mixed $default = null): mixed
    {
        return $this->inputs->queryValue(key: $key, default: $default);
    }

    public function fromBody(string $key, mixed $default = null): mixed
    {
        return $this->inputs->bodyValue(key: $key, default: $default);
    }

    public function sanitized(): SanitizedRequestedInputs
    {
        return new SanitizedRequestedInputs(
            inputs: $this,
            sanitizer: $this->sanitizer,
        );
    }

    public function sanitizedHtml(string $key, string $default = ''): string
    {
        return $this->sanitizer->html(
            value: $this->get(key: $key, default: $default),
        );
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $dtoClass
     *
     * @return T
     */
    public function as(string $dtoClass): object
    {
        return $this->mapper->map(
            inputs: $this,
            dtoClass: $dtoClass,
        );
    }
}
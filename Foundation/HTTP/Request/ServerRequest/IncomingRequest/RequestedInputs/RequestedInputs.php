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
 * RequestedInputs - Capability owner for typed and sanitized input access.
 *
 * Semantic rules:
 * - key presence uses array_key_exists() (null means "present but null")
 * - body wins over query when the same key exists in both
 * - sanitization is an externalized view via sanitized() / sanitizedHtml()
 * - DTO creation is delegated via as()
 */
final readonly class RequestedInputs
{
    public function __construct(
        private Inputs                  $merged,
        private InputSanitizer          $sanitizer,
        private MapRequestedInputsToDto $mapper,
    ) {}

    public function all(): array
    {
        return $this->merged->all();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->merged->get(key: $key, default: $default);
    }

    public function has(string $key): bool
    {
        return $this->merged->has(key: $key);
    }

    public function hasNonNull(string $key): bool
    {
        return $this->merged->hasNonNull(key: $key);
    }

    public function string(string $key, string $default = ''): string
    {
        return $this->merged->string(key: $key, default: $default);
    }

    public function int(string $key, int $default = 0): int
    {
        return $this->merged->int(key: $key, default: $default);
    }

    public function float(string $key, float $default = 0.0): float
    {
        return $this->merged->float(key: $key, default: $default);
    }

    public function bool(string $key, bool $default = false): bool
    {
        return $this->merged->bool(key: $key, default: $default);
    }

    public function array(string $key, array $default = []): array
    {
        return $this->merged->array(key: $key, default: $default);
    }

    /**
     * @template T of BackedEnum
     * @param class-string<T> $enumClass
     * @return T|null
     */
    public function enum(string $key, string $enumClass, mixed $default = null): ?BackedEnum
    {
        return $this->merged->enum(key: $key, enumClass: $enumClass, default: $default);
    }

    public function only(string ...$keys): array
    {
        return $this->merged->only(keys: $keys);
    }

    public function except(string ...$keys): array
    {
        return $this->merged->except(keys: $keys);
    }

    public function value(string $key, mixed $default = null): InputValue
    {
        return $this->merged->value(key: $key, default: $default);
    }

    public function fromQuery(string $key, mixed $default = null): mixed
    {
        return $this->merged->fromQuery(key: $key, default: $default);
    }

    public function fromBody(string $key, mixed $default = null): mixed
    {
        return $this->merged->fromBody(key: $key, default: $default);
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
        return $this->sanitizer->html(value: $this->get(key: $key, default: $default));
    }

    /**
     * @template T of object
     * @param class-string<T> $dtoClass
     * @return T
     */
    public function as(string $dtoClass): object
    {
        return $this->mapper->map(inputs: $this, dtoClass: $dtoClass);
    }
}

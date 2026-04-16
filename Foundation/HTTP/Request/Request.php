<?php

declare(strict_types=1);

namespace Avax\HTTP\Request;

use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use ReflectionException;

/**
 * Request - Base class for HTTP Request-backed DTOs.
 *
 * Enables declarative request handling using PHP 8 attributes.
 * Automatically hydrates from ServerRequest inputs with validation.
 *
 * Usage:
 * ```php
 * class CreateNewProjectRequest extends \Avax\HTTP\Request\Request
 * {
 *     #[Required]
 *     #[Min(3)]
 *     public string $projectName;
 *
 *     #[Required]
 *     #[Integer]
 *     #[Min(1)]
 *     public int $projectNumber;
 * }
 *
 * // Usage in controller:
 * $request = CreateNewProjectRequest::fromRequest($httpRequest);
 * $request->projectName; // already validated!
 * ```
 */
abstract class Request extends AbstractDTO
{
    /**
     * Create a Request DTO instance from ServerRequest.
     *
     * @throws ReflectionException
     */
    public static function fromRequest(ServerRequest $request): static
    {
        return new static(data: $request->inputs()->all());
    }

    /**
     * Create a Request DTO from raw input array.
     *
     * @throws ReflectionException
     */
    public static function fromInputs(array $inputs): static
    {
        return new static(data: $inputs);
    }
}

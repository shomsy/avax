<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\Inputs\DTO;

use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;
use ReflectionException;

/**
 * QueryParamsDTO - Strongly typed query parameters DTO.
 *
 * Provides automatic type casting for query parameters.
 * Supports nested DTOs, arrays of DTOs, and BackedEnums.
 */
class QueryParamsDTO extends AbstractDTO
{
    /**
     * @throws ReflectionException
     */
    public static function fromArray(array $params): self
    {
        return new self(data: $params);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->{$key} ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($this->{$key});
    }

    public function only(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            if ($this->has($key)) {
                $result[$key] = $this->{$key};
            }
        }
        return $result;
    }

    public function except(array $keys): array
    {
        $result = [];
        foreach (get_object_vars($this) as $key => $value) {
            if (!in_array($key, $keys, true)) {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}

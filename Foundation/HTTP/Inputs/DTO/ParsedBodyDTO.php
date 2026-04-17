<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\Inputs\DTO;

use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;

/**
 * ParsedBodyDTO - Strongly typed parsed body DTO.
 *
 * Provides automatic type casting and validation for parsed body data.
 * Supports nested DTOs, arrays of DTOs, and BackedEnums.
 */
class ParsedBodyDTO extends AbstractDTO
{
    /**
     * @throws \ReflectionException
     */
    public static function fromArray(array|object|null $data): self
    {
        if ($data === null) {
            return new self(data: []);
        }

        if (is_object($data) && !$data instanceof self) {
            $data = (array) $data;
        }

        return new self(data: is_array($data) ? $data : []);
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
            if ($this->has(key: $key)) {
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

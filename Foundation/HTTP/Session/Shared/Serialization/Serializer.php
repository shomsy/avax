<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Serialization;

use InvalidArgumentException;

final class Serializer
{
    public function safeUnserialize(string $data) : mixed
    {
        $result = @unserialize(data: $data, options: ['allowed_classes' => false]);

        if ($result === false && $data !== serialize(value: false)) {
            throw new InvalidArgumentException(message: 'Malformed serialized session payload.');
        }

        return $result;
    }
}

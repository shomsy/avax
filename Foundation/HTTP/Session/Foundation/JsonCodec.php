<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Foundation;

use JsonException;

final class JsonCodec
{
    public function tryEncode(mixed $value) : ?string
    {
        try {
            return $this->encode($value);
        } catch (JsonException) {
            return null;
        }
    }

    public function encode(mixed $value) : string
    {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    public function tryDecode(string $data) : mixed
    {
        try {
            return $this->decode($data);
        } catch (JsonException) {
            return null;
        }
    }

    public function decode(string $data) : mixed
    {
        return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
    }
}
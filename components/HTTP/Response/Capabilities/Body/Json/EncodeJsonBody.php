<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Body\Json;

use JsonException;

final class EncodeJsonBody
{
    /**
     * @throws JsonException
     */
    public function __invoke(mixed $data) : string
    {
        return json_encode(value: $data, flags: JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

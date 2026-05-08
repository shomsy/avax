<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Transform;

use InvalidArgumentException;

/**
 * Converts collection to JSON.
 */
final readonly class ConvertCollectionToJson
{
    public function __construct(
        private array $items = [],
    ) {
    }

    public function __invoke(int $flags = 0): string
    {
        return $this->toJson(flags: $flags);
    }

    public function toJson(int $flags = 0): string
    {
        $json = json_encode(value: $this->items, flags: $flags);

        if ($json === false) {
            throw new InvalidArgumentException(
                message: 'Failed to encode collection to JSON: '.json_last_error_msg(),
            );
        }

        return $json;
    }

    public function getItems(): array
    {
        return $this->items;
    }
}

<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\Normalize;

use JsonSerializable;
use Traversable;

/**
 * NormalizeData - Flow to standardize varied input data into a canonical array format.
 */
final readonly class NormalizeData
{
    public function execute(mixed $data) : array
    {
        if (is_array($data)) {
            return $data;
        }

        if ($data instanceof JsonSerializable) {
            return $data->jsonSerialize();
        }

        if ($data instanceof Traversable) {
            return iterator_to_array($data);
        }

        return (array) $data;
    }
}

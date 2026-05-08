<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Flows\SerializeDataObject;

final readonly class ConvertDataObjectToFlatArray
{
    /**
     * @return array<array-key, mixed>
     */
    public function convert(object $object) : array
    {
        return get_object_vars(object: $object);
    }
}

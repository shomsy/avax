<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\SerializeDataObject;

final readonly class ConvertDataObjectToFlatArray
{
    public function convert(object $object) : array
    {
        return get_object_vars(object: $object);
    }
}

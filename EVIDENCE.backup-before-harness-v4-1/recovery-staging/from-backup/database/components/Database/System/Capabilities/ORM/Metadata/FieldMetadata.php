<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\ORM\Metadata;

final readonly class FieldMetadata
{
    public function __construct(
        public string  $property,
        public string  $column,
        public ?string $type = null,
        public bool    $id = false,
        public bool    $generated = false,
        public bool    $nullable = false
    ) {}
}

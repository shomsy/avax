<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\ORM\Metadata;

final readonly class FieldMetadata
{
    public function __construct(
        public string      $property,
        public string      $column,
        public string|null $type = null,
        public bool        $id = false,
        public bool        $generated = false,
        public bool        $nullable = false
    ) {}
}

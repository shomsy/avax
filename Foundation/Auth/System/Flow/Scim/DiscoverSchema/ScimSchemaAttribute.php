<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\DiscoverSchema;

/**
 * Represents a SCIM schema attribute.
 */
final readonly class ScimSchemaAttribute
{
    public function __construct(
        public string $name,
        public string $type,
        public bool $required = false,
        public bool $multi = false
    ) {}
}
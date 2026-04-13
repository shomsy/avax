<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\DiscoverSchema;

/**
 * Represents a SCIM schema.
 *
 * @param list<ScimSchemaAttribute> $attributes
 */
final readonly class ScimSchema
{
    /**
     * @param list<ScimSchemaAttribute> $attributes
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public array $attributes
    ) {}
}
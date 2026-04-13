<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\DiscoverSchema;

/**
 * Represents a SCIM resource type.
 */
final readonly class ScimResourceType
{
    public function __construct(
        public string $name,
        public string $endpoint,
        public string $schema
    ) {}
}
<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\DiscoverSchema;

/**
 * Result of SCIM schema discovery.
 *
 * @param list<ScimSchema> $schemas
 * @param list<ScimResourceType> $resourceTypes
 */
final readonly class ScimSchemaDiscoveryResult
{
    /**
     * @param list<ScimSchema> $schemas
     * @param list<ScimResourceType> $resourceTypes
     */
    public function __construct(
        public array $schemas,
        public array $resourceTypes
    ) {}
}
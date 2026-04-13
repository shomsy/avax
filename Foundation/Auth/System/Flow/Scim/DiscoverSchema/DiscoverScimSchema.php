<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\DiscoverSchema;

/**
 * SCIM schema discovery endpoint.
 *
 * Returns the supported schemas and resource types.
 */
final readonly class DiscoverScimSchema
{
    /**
     * Returns the schema discovery result.
     */
    public function execute() : ScimSchemaDiscoveryResult
    {
        return new ScimSchemaDiscoveryResult(
            schemas: $this->defaultSchemas(),
            resourceTypes: $this->defaultResourceTypes()
        );
    }

    /**
     * @return list<ScimSchema>
     */
    private function defaultSchemas() : array
    {
        return [
            new ScimSchema(
                id: 'urn:ietf:params:scim:schemas:core:2.0:User',
                name: 'User',
                description: 'User account',
                attributes: [
                    new ScimSchemaAttribute(name: 'userName', type: 'string', required: true),
                    new ScimSchemaAttribute(name: 'name', type: 'complex'),
                    new ScimSchemaAttribute(name: 'emails', type: 'complex', multi: true),
                    new ScimSchemaAttribute(name: 'groups', type: 'complex', multi: true),
                    new ScimSchemaAttribute(name: 'active', type: 'boolean'),
                ]
            ),
            new ScimSchema(
                id: 'urn:ietf:params:scim:schemas:core:2.0:Group',
                name: 'Group',
                description: 'Group of users',
                attributes: [
                    new ScimSchemaAttribute(name: 'displayName', type: 'string', required: true),
                    new ScimSchemaAttribute(name: 'members', type: 'complex', multi: true),
                ]
            ),
        ];
    }

    /**
     * @return list<ScimResourceType>
     */
    private function defaultResourceTypes() : array
    {
        return [
            new ScimResourceType(
                name: 'User',
                endpoint: '/Users',
                schema: 'urn:ietf:params:scim:schemas:core:2.0:User'
            ),
            new ScimResourceType(
                name: 'Group',
                endpoint: '/Groups',
                schema: 'urn:ietf:params:scim:schemas:core:2.0:Group'
            ),
        ];
    }
}
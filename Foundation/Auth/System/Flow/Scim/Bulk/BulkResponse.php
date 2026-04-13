<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\Bulk;

/**
 * Data transfer object for a bulk response.
 */
final readonly class BulkResponse
{
    public function __construct(
        public readonly array $operations,
        public readonly string $schemas
    ) {}

    /**
     * Creates a BulkResponse from raw data.
     */
    public static function fromArray(array $operations) : self
    {
        $operationResults = [];
        foreach ($operations as $operation) {
            $operationResults[] = [
                'status' => $operation->status,
                'location' => $operation->path,
                'response' => $operation->body
            ];
        }

        return new self(
            operations: $operationResults,
            schemas: 'urn:ietf:params:scim:api:messages:2.0:BulkResponse'
        );
    }
}
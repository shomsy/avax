<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\Bulk;

use Avax\Auth\System\Capability\Scim\ScimFailed;

/**
 * Data transfer object for a bulk request.
 */
final readonly class BulkRequest
{
    public function __construct(
        public readonly array $operations,
        public readonly bool $failOnErrors,
        public readonly int $maxOperations
    ) {}

    /**
     * Creates a BulkRequest from raw data.
     *
     * @throws ScimFailed
     */
    public static function fromArray(array $data) : self
    {
        $operationsData = $data['Operations'] ?? [];
        if (!is_array($operationsData)) {
            throw ScimFailed::invalidBulkRequest('Operations must be an array');
        }

        $operations = [];
        foreach ($operationsData as $operationData) {
            $operations[] = Operation::fromArray($operationData);
        }

        $failOnErrors = $data['failOnErrors'] ?? true;
        if (!is_bool($failOnErrors)) {
            throw ScimFailed::invalidBulkRequest('failOnErrors must be a boolean');
        }

        $maxOperations = $data['maxOperations'] ?? 100;
        if (!is_int($maxOperations) || $maxOperations <= 0) {
            throw ScimFailed::invalidBulkRequest('maxOperations must be a positive integer');
        }

        return new self(
            operations: $operations,
            failOnErrors: $failOnErrors,
            maxOperations: $maxOperations
        );
    }
}
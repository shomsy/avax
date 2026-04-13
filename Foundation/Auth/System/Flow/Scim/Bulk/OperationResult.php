<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\Bulk;

use Avax\Auth\System\Capability\Scim\ScimFailed;

/**
 * Data transfer object for the result of a single bulk operation.
 */
final readonly class OperationResult
{
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly int $status,
        public readonly array $body,
        public readonly bool $bulkId
    ) {}

    /**
     * Creates an OperationResult from raw data.
     */
    public static function fromArray(array $data, string $method, string $path, bool $bulkId) : self
    {
        return new self(
            method: $method,
            path: $path,
            status: $data['status'] ?? 200,
            body: $data['body'] ?? [],
            bulkId: $bulkId
        );
    }
}
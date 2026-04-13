<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\Bulk;

use Avax\Auth\System\Capability\Scim\ScimFailed;

/**
 * Data transfer object for a single bulk operation.
 */
final readonly class Operation
{
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $body,
        public readonly bool $bulkId
    ) {}

    /**
     * Creates an Operation from raw data.
     *
     * @throws ScimFailed
     */
    public static function fromArray(array $data) : self
    {
        if (!isset($data['method']) || !is_string($data['method'])) {
            throw ScimFailed::invalidBulkRequest('method is required and must be a string');
        }

        if (!isset($data['path']) || !is_string($data['path'])) {
            throw ScimFailed::invalidBulkRequest('path is required and must be a string');
        }

        $body = $data['body'] ?? [];
        if (!is_array($body)) {
            throw ScimFailed::invalidBulkRequest('body must be an array');
        }

        $bulkId = $data['bulkId'] ?? false;
        if (!is_bool($bulkId)) {
            throw ScimFailed::invalidBulkRequest('bulkId must be a boolean');
        }

        return new self(
            method: strtoupper(trim($data['method'])),
            path: $data['path'],
            body: $body,
            bulkId: $bulkId
        );
    }
}
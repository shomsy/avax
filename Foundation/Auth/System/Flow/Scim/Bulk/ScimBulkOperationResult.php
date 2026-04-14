<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\Bulk;

final readonly class ScimBulkOperationResult
{
    /**
     * @param array<string, mixed> $response
     */
    public function __construct(
        public string $method,
        public string $path,
        public int $status,
        public array $response = [],
        public string|null $bulkId = null
    ) {}
}

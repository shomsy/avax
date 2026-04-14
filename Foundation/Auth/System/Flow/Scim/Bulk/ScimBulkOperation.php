<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\Bulk;

final readonly class ScimBulkOperation
{
    /**
     * @param array<string, mixed> $body
     */
    public function __construct(
        public string $method,
        public string $path,
        public array $body = [],
        public string|null $bulkId = null
    ) {}
}

<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\Bulk;

final readonly class ScimBulkResponse
{
    /**
     * @param list<ScimBulkOperationResult> $operations
     */
    public function __construct(
        public array $operations
    ) {}
}

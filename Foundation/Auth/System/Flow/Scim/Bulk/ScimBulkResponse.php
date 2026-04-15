<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\Bulk;

final readonly class ScimBulkResponse
{
    public array $operations;

    /**
     * @param list<ScimBulkOperationResult> $operations
     */
    public function __construct(
        array $operations
    )
    {
        $this->operations = $operations;
    }
}

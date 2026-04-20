<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk;

final readonly class ScimBulkResponse
{
    /** @var list<ScimBulkOperationResult> */
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

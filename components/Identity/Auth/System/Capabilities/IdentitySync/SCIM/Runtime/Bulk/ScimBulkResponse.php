<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk;

final readonly class ScimBulkResponse
{
    /**
     * @param list<ScimBulkOperationResult> $operations
     */
    public function __construct(public array $operations) {}
}

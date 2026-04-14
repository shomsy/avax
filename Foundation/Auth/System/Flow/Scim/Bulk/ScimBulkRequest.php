<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\Bulk;

use SensitiveParameter;

final readonly class ScimBulkRequest
{
    /**
     * @param list<ScimBulkOperation> $operations
     */
    public function __construct(
        public string $directoryId,
        #[SensitiveParameter] public string $directoryToken,
        public array $operations
    ) {}
}

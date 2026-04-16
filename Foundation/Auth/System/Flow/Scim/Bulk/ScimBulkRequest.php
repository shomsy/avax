<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\Bulk;

use SensitiveParameter;

final readonly class ScimBulkRequest
{
    /** @var list<ScimBulkOperation> */
    public array  $operations;
    public string $directoryToken;
    public string $directoryId;

    /**
     * @param list<ScimBulkOperation> $operations
     */
    public function __construct(
        string                       $directoryId,
        #[SensitiveParameter] string $directoryToken,
        array                        $operations
    )
    {
        $this->directoryId    = $directoryId;
        $this->directoryToken = $directoryToken;
        $this->operations     = $operations;
    }
}

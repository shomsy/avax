<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk;

final readonly class ScimBulkOperationResult
{
    /** @var array<string, mixed> */
    public array $response;

    /**
     * @param array<string, mixed> $response
     */
    public function __construct(
        public string $method,
        public string $path,
        public int $status,
        array $response = [],
        public ?string $bulkId = null,
    ) {
        $response ??= [];
        $this->response = $response;
    }
}

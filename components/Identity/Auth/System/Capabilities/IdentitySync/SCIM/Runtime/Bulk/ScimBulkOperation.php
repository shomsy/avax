<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk;

final readonly class ScimBulkOperation
{
    /** @var array<string, mixed> */
    public array $body;

    /**
     * @param array<string, mixed> $body
     */
    public function __construct(
        public string      $method,
        public string      $path,
        array|null = null,
        public string|null $bulkId = null,
    )
    {
        $body ??= [];
        $this->body = $body;
    }
}

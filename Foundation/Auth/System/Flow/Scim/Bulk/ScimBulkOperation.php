<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\Bulk;

final readonly class ScimBulkOperation
{
    public string|null $bulkId;
    public array       $body;
    public string      $path;
    public string      $method;

    /**
     * @param array<string, mixed> $body
     */
    public function __construct(
        string      $method,
        string      $path,
        array|null  $body = null,
        string|null $bulkId = null
    )
    {
        $body         ??= [];
        $this->method = $method;
        $this->path   = $path;
        $this->body   = $body;
        $this->bulkId = $bulkId;
    }
}

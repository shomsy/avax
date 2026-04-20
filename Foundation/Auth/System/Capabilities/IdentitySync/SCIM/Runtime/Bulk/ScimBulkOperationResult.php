<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk;

final readonly class ScimBulkOperationResult
{
    public string|null $bulkId;
    /** @var array<string, mixed> */
    public array       $response;
    public int         $status;
    public string      $path;
    public string      $method;

    /**
     * @param array<string, mixed> $response
     */
    public function __construct(
        string      $method,
        string      $path,
        int         $status,
        array|null  $response = null,
        string|null $bulkId = null
    )
    {
        $response       ??= [];
        $this->method   = $method;
        $this->path     = $path;
        $this->status   = $status;
        $this->response = $response;
        $this->bulkId   = $bulkId;
    }
}

<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Foundation\Failure;

use RuntimeException;

final class TenantNotFoundException extends RuntimeException
{
    public function __construct(string $identifier, string|null $message = null)
    {
        $message ??= "Tenant not found: {$identifier}";
        parent::__construct($message, 404);
    }
}

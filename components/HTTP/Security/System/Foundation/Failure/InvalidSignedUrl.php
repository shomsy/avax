<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Security\System\Foundation\Failure;

final class InvalidSignedUrl extends SecurityException
{
    public function __construct(string $message = 'Invalid or expired signed URL.')
    {
        parent::__construct($message, 403);
    }
}

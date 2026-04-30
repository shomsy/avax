<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Access\RequireResourceOwner;

use RuntimeException;

final class ResourceOwnerDenied extends RuntimeException
{
    public function __construct(
        private readonly int $ownerUserId,
        string $message = 'Current user does not own this resource.',
    )
    {
        parent::__construct(message: $message, code: 403);
    }

    public function ownerUserId() : int
    {
        return $this->ownerUserId;
    }
}

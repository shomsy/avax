<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Access\RequireResourceOwner;

use RuntimeException;

final class ResourceOwnerDenied extends RuntimeException
{
    private readonly int $ownerUserId;

    public function __construct(
        int    $ownerUserId,
        string $message = 'Current user does not own this resource.'
    )
    {
        $this->ownerUserId = $ownerUserId;
        parent::__construct(message: $message, code: 403);
    }

    public function ownerUserId() : int
    {
        return $this->ownerUserId;
    }
}

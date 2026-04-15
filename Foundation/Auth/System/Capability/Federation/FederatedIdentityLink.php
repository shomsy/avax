<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Federation;

final readonly class FederatedIdentityLink
{
    public int    $userId;
    public string $subject;
    public string $connectionId;

    public function __construct(
        string $connectionId,
        string $subject,
        int    $userId
    )
    {
        $this->connectionId = $connectionId;
        $this->subject      = $subject;
        $this->userId       = $userId;
    }
}

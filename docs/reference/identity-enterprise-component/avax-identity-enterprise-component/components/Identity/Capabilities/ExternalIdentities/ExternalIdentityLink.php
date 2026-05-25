<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\ExternalIdentities;

use Avax\Components\Identity\Foundation\Values\ExternalProvider;
use Avax\Components\Identity\Foundation\Values\ExternalSubject;
use Avax\Components\Identity\Foundation\Values\UserId;

final readonly class ExternalIdentityLink
{
    public function __construct(private ExternalProvider $provider, private ExternalSubject $subject, private UserId $userId) {}

    public function provider(): ExternalProvider
    {
        return $this->provider;
    }

    public function subject(): ExternalSubject
    {
        return $this->subject;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }
}

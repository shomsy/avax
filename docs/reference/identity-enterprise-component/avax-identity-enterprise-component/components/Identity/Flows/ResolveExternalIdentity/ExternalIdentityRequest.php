<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Flows\ResolveExternalIdentity;

use Avax\Components\Identity\Foundation\Values\ExternalProvider;
use Avax\Components\Identity\Foundation\Values\ExternalSubject;

final readonly class ExternalIdentityRequest
{
    public function __construct(private ExternalProvider $provider, private ExternalSubject $subject) {}

    public function provider(): ExternalProvider
    {
        return $this->provider;
    }

    public function subject(): ExternalSubject
    {
        return $this->subject;
    }
}

<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Flows\ResolveExternalIdentity;

use Avax\Components\Identity\Capabilities\ExternalIdentities\ExternalIdentityDirectory;

final readonly class ResolveExternalIdentity
{
    public function __construct(private ExternalIdentityDirectory $externalIdentities) {}

    public function resolve(ExternalIdentityRequest $request): ExternalIdentityResult
    {
        $link = $this->externalIdentities->find($request->provider(), $request->subject());

        return $link === null ? ExternalIdentityResult::missing() : ExternalIdentityResult::found($link->userId());
    }
}

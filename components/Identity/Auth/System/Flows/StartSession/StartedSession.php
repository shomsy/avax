<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\StartSession;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Store\IdentitySession;

/**
 * StartedSession — value object wrapping a newly started session.
 *
 * Adapted from the enterprise reference package.
 */
final readonly class StartedSession
{
    public function __construct(private IdentitySession $session) {}

    public function session(): IdentitySession
    {
        return $this->session;
    }
}

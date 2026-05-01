<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Support\SenderConstraint;

/**
 * Sender-constrained token modes supported by the OAuth lane.
 */
enum OAuthSenderConstraintType: string
{
    case DPOP = 'dpop';
    case MTLS = 'mtls';
}

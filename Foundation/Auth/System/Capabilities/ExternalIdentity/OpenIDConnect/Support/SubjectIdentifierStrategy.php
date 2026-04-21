<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support;

/**
 * Subject identifier strategy for OIDC.
 */
enum SubjectIdentifierStrategy: string
{
    case PUBLIC   = 'public';
    case PAIRWISE = 'pairwise';
}

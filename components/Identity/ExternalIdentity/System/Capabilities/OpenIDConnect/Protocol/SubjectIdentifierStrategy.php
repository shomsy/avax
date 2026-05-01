<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol;

/**
 * Subject identifier strategy for OIDC.
 */
enum SubjectIdentifierStrategy: string
{
    case PUBLIC   = 'public';
    case PAIRWISE = 'pairwise';
}

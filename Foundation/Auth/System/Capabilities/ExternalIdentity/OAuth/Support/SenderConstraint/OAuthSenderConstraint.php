<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint;

/**
 * Bound proof material attached to a sender-constrained OAuth token.
 */
final readonly class OAuthSenderConstraint
{
    public function __construct(public OAuthSenderConstraintType $type, public string $thumbprint)
    {
    }

    public function equals(self $other) : bool
    {
        return $this->type === $other->type
            && hash_equals($this->thumbprint, $other->thumbprint);
    }
}

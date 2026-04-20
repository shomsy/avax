<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\OAuth\SenderConstraint;

/**
 * Bound proof material attached to a sender-constrained OAuth token.
 */
final readonly class OAuthSenderConstraint
{
    public string                    $thumbprint;
    public OAuthSenderConstraintType $type;

    public function __construct(
        OAuthSenderConstraintType $type,
        string                    $thumbprint
    )
    {
        $this->type       = $type;
        $this->thumbprint = $thumbprint;
    }

    public function equals(self $other) : bool
    {
        return $this->type === $other->type
            && hash_equals($this->thumbprint, $other->thumbprint);
    }
}

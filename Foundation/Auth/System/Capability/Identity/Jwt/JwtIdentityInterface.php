<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Identity\Jwt;

use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Flow\Token\IssuedRefreshToken;
use Avax\Auth\System\Flow\Token\IssuedToken;
use Avax\Auth\System\Flow\Token\ResolvedToken;
use Avax\Auth\System\Flow\Token\TokenIssuerInterface;
use Avax\Auth\System\Flow\Token\TokenVerifierInterface;
use DateTimeImmutable;

/**
 * Interface JwtIdentityInterface within the Auth System.
 */
interface JwtIdentityInterface extends TokenIssuerInterface, TokenVerifierInterface
{
    public function issue(User $user, DateTimeImmutable|null $mfaVerifiedAt = null) : IssuedToken;

    public function resolve(string $token) : ResolvedToken|null;

    public function issueRefreshToken(User $user, DateTimeImmutable|null $mfaVerifiedAt = null) : IssuedRefreshToken|null;

    public function revoke(string $tokenId, DateTimeImmutable $expiresAt) : void;
}

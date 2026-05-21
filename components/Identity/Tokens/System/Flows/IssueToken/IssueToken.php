<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Flows\IssueToken;

use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec\TokenCodecInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Record\IssuedToken;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Issues a new token for the given subject.
 */
final readonly class IssueToken
{
    public function __construct(
        #[SensitiveParameter]
        private TokenCodecInterface $tokenCodec,
    ) {}

    public function execute(TokenSubject $subject) : IssuedToken
    {
        $tokenId   = bin2hex(random_bytes(16));
        $issuedAt  = new DateTimeImmutable();
        $expiresAt = $issuedAt->modify('+1 hour');

        $claims = array_merge($subject->claims, [
            'sub' => $subject->userId,
            'jti' => $tokenId,
            'iat' => $issuedAt->getTimestamp(),
            'exp' => $expiresAt->getTimestamp(),
        ]);

        $token = $this->tokenCodec->encode($claims);

        return new IssuedToken(
            token     : $token,
            tokenId   : $tokenId,
            expiresAt : $expiresAt,
        );
    }
}

<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration\Graphs;

use Avax\Components\Identity\Auth\System\Flows\IssueAccessToken\IssueAccessToken;
use Avax\Components\Identity\Auth\System\Flows\VerifyAccessToken\VerifyAccessToken;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Blacklist\TokenBlacklist;

/**
 * TokenGraph — composition root for token issuing and verification.
 *
 * Adapted from the enterprise reference package.
 * Groups the IssueAccessToken and VerifyAccessToken flows with the
 * TokenBlacklist for runtime token lifecycle management.
 */
final readonly class TokenGraph
{
    public function __construct(
        private IssueAccessToken $issueAccessToken,
        private VerifyAccessToken $verifyAccessToken,
        private TokenBlacklist $blacklist,
    ) {}

    public function issueAccessToken(): IssueAccessToken
    {
        return $this->issueAccessToken;
    }

    public function verifyAccessToken(): VerifyAccessToken
    {
        return $this->verifyAccessToken;
    }

    public function blacklist(): TokenBlacklist
    {
        return $this->blacklist;
    }
}

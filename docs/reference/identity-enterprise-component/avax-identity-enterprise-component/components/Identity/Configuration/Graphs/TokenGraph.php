<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Configuration\Graphs;

use Avax\Components\Identity\Capabilities\Tokens\TokenBlacklist;
use Avax\Components\Identity\Flows\IssueAccessToken\IssueAccessToken;
use Avax\Components\Identity\Flows\VerifyAccessToken\VerifyAccessToken;

final readonly class TokenGraph
{
    public function __construct(private IssueAccessToken $issueAccessToken, private VerifyAccessToken $verifyAccessToken, private TokenBlacklist $blacklist) {}

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

<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Flows\ExchangeToken;

use Avax\Framework\Foundation\Exception\NotImplementedException;

/**
 * ExchangeAuthorizationCode - OAuth2 flow to exchange code for tokens.
 */
final readonly class ExchangeAuthorizationCode
{
    public function execute() : object
    {
        throw new NotImplementedException('OAuth2 code exchange flow not yet implemented');
    }
}

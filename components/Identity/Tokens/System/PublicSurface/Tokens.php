<?php
declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\PublicSurface;

use Avax\Components\Identity\Tokens\System\Flows\ExchangeToken\ExchangeAuthorizationCode;
use Avax\Components\Identity\Tokens\System\Capabilities\TokenCodec;

/**
 * Tokens - Main entry point for Identity/Tokens component.
 */
final readonly class Tokens implements TokensInterface
{
    public function __construct(
        private ExchangeAuthorizationCode $exchangeCodeFlow,
        private TokenCodec                $codec
    ) {}

    public function authorize(array $request) : object
    {
        // Implementation
        return (object)[];
    }

    public function exchangeCode(string $code) : object
    {
        return $this->exchangeCodeFlow->execute($code);
    }

    public function introspect(string $token) : object
    {
        // Implementation
        return (object)[];
    }

    public function revoke(string $token) : void
    {
        // Implementation
    }
}

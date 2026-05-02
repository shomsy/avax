<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Security\System\Capabilities\Csrf;

use Avax\Components\HTTP\Session\System\PublicSurface\Session;
use Psr\Log\LoggerInterface;

final readonly class CsrfTokens
{
    private const string SESSION_KEY = '_csrf_tokens';

    private const int TOKEN_EXPIRATION_MINUTES = 30;

    private const int    MAX_TOKENS_PER_SESSION = 5;

    public function __construct(
        private Session $session,
        private LoggerInterface $logger,
        private int $tokenExpirationMinutes = self::TOKEN_EXPIRATION_MINUTES,
        private int $maxTokensPerSession = self::MAX_TOKENS_PER_SESSION,
    ) {}

    public function getToken(): string
    {
        $tokens = $this->pruneExcessTokens($this->pruneExpiredTokens($this->getTokens()));
        $activeToken = $this->readMostRecentToken($tokens);

        if ($activeToken !== null) {
            $this->storeTokens($tokens);

            return $activeToken;
        }

        $newToken = $this->generateToken();
        $tokens[$newToken] = time();
        $this->storeTokens($tokens);

        $this->logger->info('Generated CSRF token.', ['token_count' => count($tokens)]);

        return $newToken;
    }

    public function validateToken(?string $token): bool
    {
        if ($token === null) {
            return false;
        }

        $tokens = $this->pruneExpiredTokens($this->getTokens());

        if (! isset($tokens[$token])) {
            $this->logger->warning('CSRF validation failed: Invalid or missing token.');

            return false;
        }

        // Consume token
        unset($tokens[$token]);
        $this->storeTokens($tokens);

        $this->logger->info('CSRF token validated and consumed.');

        return true;
    }

    private function getTokens(): array
    {
        return $this->session->get(self::SESSION_KEY, []);
    }

    private function storeTokens(array $tokens): void
    {
        $this->session->put(self::SESSION_KEY, $tokens);
    }

    private function pruneExcessTokens(array $tokens): array
    {
        if (count($tokens) <= $this->maxTokensPerSession) {
            return $tokens;
        }

        asort($tokens);

        return array_slice($tokens, -$this->maxTokensPerSession, null, true);
    }

    private function pruneExpiredTokens(array $tokens): array
    {
        $now = time();
        $expiry = $this->tokenExpirationMinutes * 60;

        return array_filter($tokens, static fn ($ts) : bool => $now - $ts <= $expiry);
    }

    private function readMostRecentToken(array $tokens): ?string
    {
        if ($tokens === []) {
            return null;
        }

        arsort($tokens);

        return (string) array_key_first($tokens);
    }

    private function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}

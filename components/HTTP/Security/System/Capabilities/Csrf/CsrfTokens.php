<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Security\System\Capabilities\Csrf;

use Avax\Components\HTTP\Session\System\PublicSurface\SessionInterface;
use Psr\Log\LoggerInterface;
use SensitiveParameter;

final readonly class CsrfTokens
{
    private const string SESSION_KEY = '_csrf_tokens';
    private const int TOKEN_EXPIRATION_MINUTES = 30;
    private const int MAX_TOKENS_PER_SESSION = 5;

    public function __construct(
        #[SensitiveParameter] private SessionInterface $session,
        private LoggerInterface $logger,
        private int $tokenExpirationMinutes = self::TOKEN_EXPIRATION_MINUTES,
        private int $maxTokensPerSession = self::MAX_TOKENS_PER_SESSION
    ) {
    }

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

    public function validateToken(#[SensitiveParameter] ?string $token): bool
    {
        $tokens = $this->pruneExpiredTokens($this->getTokens());

        if ($token === null || !isset($tokens[$token])) {
            $this->logger->warning('CSRF validation failed: Missing or invalid token.', [
                'token_present' => $token !== null
            ]);
            return false;
        }

        if (time() - $tokens[$token] > $this->tokenExpirationMinutes * 60) {
            $this->logger->info('CSRF token expired.');
            unset($tokens[$token]);
            $this->storeTokens($tokens);
            return false;
        }

        unset($tokens[$token]);
        $this->storeTokens($tokens);

        $this->logger->info('CSRF token validated and consumed.', [
            'remaining_token_count' => count($tokens)
        ]);

        return true;
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
        $currentTime = time();
        return array_filter(
            $tokens,
            fn($timestamp) => is_int($timestamp) && $currentTime - $timestamp <= $this->tokenExpirationMinutes * 60
        );
    }

    private function getTokens(): array
    {
        $tokens = $this->session->get(self::SESSION_KEY, []);
        return is_array($tokens) ? $tokens : [];
    }

    private function storeTokens(array $tokens): void
    {
        $this->session->set(self::SESSION_KEY, $tokens);
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

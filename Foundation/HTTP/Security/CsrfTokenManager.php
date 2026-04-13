<?php

declare(strict_types=1);

namespace Avax\HTTP\Security;

use Avax\HTTP\Session\Session;
use Exception;
use Psr\Log\LoggerInterface;
use Random\RandomException;
use SensitiveParameter;

/**
 * The `CsrfTokenManager` is a high-level component that manages CSRF tokens
 * to prevent cross-site request forgery attacks.
 */
final readonly class CsrfTokenManager
{
    /**
     * The session key under which all CSRF tokens are stored.
     */
    private const string SESSION_KEY = '_csrf_tokens';

    /**
     * The number of minutes before a CSRF token expires.
     */
    private const int TOKEN_EXPIRATION_MINUTES = 30;

    /**
     * The maximum number of tokens allowed per session.
     */
    private const int MAX_TOKENS_PER_SESSION = 5;

    /**
     * @param Session         $session The session management implementation.
     * @param LoggerInterface $logger  Responsible for logging important events.
     */
    public function __construct(
        #[SensitiveParameter] private Session $session,
        private LoggerInterface               $logger,
        private int                           $tokenExpirationMinutes = self::TOKEN_EXPIRATION_MINUTES,
        private int                           $maxTokensPerSession = self::MAX_TOKENS_PER_SESSION
    ) {}

    /**
     * Retrieves or generates a CSRF token tied to the session.
     *
     * @return string The CSRF token.
     *
     * @throws Exception If the token generation process fails.
     */
    public function getToken() : string
    {
        $tokens = $this->pruneExcessTokens(tokens: $this->pruneExpiredTokens(tokens: $this->getTokens()));
        $activeToken = $this->readMostRecentToken(tokens: $tokens);

        if ($activeToken !== null) {
            $this->storeTokens(tokens: $tokens);

            return $activeToken;
        }

        $newToken          = $this->generateToken();
        $tokens[$newToken] = time();
        $this->storeTokens(tokens: $tokens);

        $this->logger->info(
            message: 'Generated CSRF token.',
            context: ['token_count' => count($tokens)]
        );

        return $newToken;
    }

    private function getTokens() : array
    {
        $tokens = $this->session->get(key: self::SESSION_KEY, default: []);

        if (! is_array($tokens)) {
            $this->logger->warning(
                message: 'CSRF tokens session value was not an array. Resetting.',
                context: ['type' => gettype($tokens)]
            );

            $this->storeTokens(tokens: []);

            return [];
        }

        return $tokens;
    }

    private function storeTokens(#[SensitiveParameter] array $tokens) : void
    {
        $this->session->put(key: self::SESSION_KEY, value: $tokens);
    }

    private function pruneExpiredTokens(#[SensitiveParameter] array $tokens) : array
    {
        $currentTime = time();

        return array_filter(
            $tokens,
            fn ($timestamp) => is_int($timestamp) && $currentTime - $timestamp <= $this->tokenExpirationMinutes * 60
        );
    }

    /**
     * @param array<string, int> $tokens
     * @return array<string, int>
     */
    private function pruneExcessTokens(#[SensitiveParameter] array $tokens) : array
    {
        if (count($tokens) <= $this->maxTokensPerSession) {
            return $tokens;
        }

        asort($tokens);
        $trimmed = array_slice($tokens, -$this->maxTokensPerSession, null, true);

        $this->logger->warning(
            message: 'Trimmed excess CSRF tokens for the session.',
            context: ['token_count' => count($tokens), 'max_tokens' => $this->maxTokensPerSession]
        );

        return $trimmed;
    }

    /**
     * @param array<string, int> $tokens
     */
    private function readMostRecentToken(#[SensitiveParameter] array $tokens) : string|null
    {
        if ($tokens === []) {
            return null;
        }

        arsort($tokens);

        return (string) array_key_first($tokens);
    }

    /**
     * @throws RandomException
     */
    private function generateToken() : string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * @throws RandomException
     */
    public function validateToken(#[SensitiveParameter] string|null $token) : bool
    {
        $tokens = $this->pruneExpiredTokens(tokens: $this->getTokens());

        if ($token === null || ! isset($tokens[$token])) {
            $this->logger->warning(
                message: 'CSRF validation failed: Missing or invalid token.',
                context: ['token_present' => $token !== null]
            );

            return false;
        }

        $isExpired = time() - $tokens[$token] > $this->tokenExpirationMinutes * 60;

        if ($isExpired) {
            $this->logger->info(message: 'CSRF token expired.');
            unset($tokens[$token]);
            $this->storeTokens(tokens: $tokens);

            return false;
        }

        unset($tokens[$token]);
        $this->storeTokens(tokens: $tokens);

        $this->logger->info(
            message: 'CSRF token validated and consumed.',
            context: ['remaining_token_count' => count($tokens)]
        );

        return true;
    }
}

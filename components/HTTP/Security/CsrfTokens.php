<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Security;

use Avax\Components\HTTP\Session\Session;
use Exception;
use Psr\Log\LoggerInterface;
use Random\RandomException;
use SensitiveParameter;

/**
 * The `CsrfTokens` is a high-level component that manages CSRF tokens
 *
 * @internal
 */
final readonly class CsrfTokens
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
    private int             $maxTokensPerSession;
    private int             $tokenExpirationMinutes;
    private LoggerInterface $logger;
    private Session         $session;

    /**
     * @param Session         $session The session management implementation.
     * @param LoggerInterface $logger  Responsible for logging important events.
     */
    public function __construct(
        #[SensitiveParameter] Session $session,
        LoggerInterface               $logger,
        int|null                      $tokenExpirationMinutes = null,
        int                           $maxTokensPerSession = self::MAX_TOKENS_PER_SESSION
    )
    {
        $tokenExpirationMinutes       ??= self::TOKEN_EXPIRATION_MINUTES;
        $this->session                = $session;
        $this->logger                 = $logger;
        $this->tokenExpirationMinutes = $tokenExpirationMinutes;
        $this->maxTokensPerSession    = $maxTokensPerSession;
    }

    /**
     * Retrieves or generates a CSRF token tied to the session.
     *
     * @return string The CSRF token.
     *
     * @throws Exception If the token generation process fails.
     */
    public function getToken() : string
    {
        $tokens      = $this->pruneExcessTokens(tokens: $this->pruneExpiredTokens(tokens: $this->getTokens()));
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
            context: ['token_count' => count(value: $tokens)]
        );

        return $newToken;
    }

    /**
     * @param array<string, int> $tokens
     *
     * @return array<string, int>
     */
    private function pruneExcessTokens(#[SensitiveParameter] array $tokens) : array
    {
        if (count(value: $tokens) <= $this->maxTokensPerSession) {
            return $tokens;
        }

        asort(array: $tokens);
        $trimmed = array_slice(array: $tokens, offset: -$this->maxTokensPerSession, length: null, preserve_keys: true);

        $this->logger->warning(
            message: 'Trimmed excess CSRF tokens for the session.',
            context: ['token_count' => count(value: $tokens), 'max_tokens' => $this->maxTokensPerSession]
        );

        return $trimmed;
    }

    private function pruneExpiredTokens(#[SensitiveParameter] array $tokens) : array
    {
        $currentTime = time();

        return array_filter(
            array   : $tokens,
            callback: fn ($timestamp) => is_int(value: $timestamp) && $currentTime - $timestamp <= $this->tokenExpirationMinutes * 60
        );
    }

    private function getTokens() : array
    {
        $tokens = $this->session->get(key: self::SESSION_KEY, default: []);

        if (! is_array(value: $tokens)) {
            $this->logger->warning(
                message: 'CSRF tokens session value was not an array. Resetting.',
                context: ['type' => gettype(value: $tokens)]
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

    /**
     * @param array<string, int> $tokens
     */
    private function readMostRecentToken(#[SensitiveParameter] array $tokens) : string|null
    {
        if ($tokens === []) {
            return null;
        }

        arsort(array: $tokens);

        return (string) array_key_first(array: $tokens);
    }

    /**
     * @throws RandomException
     */
    private function generateToken() : string
    {
        return bin2hex(string: random_bytes(length: 32));
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
            context: ['remaining_token_count' => count(value: $tokens)]
        );

        return true;
    }
}

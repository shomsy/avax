<?php

declare(strict_types=1);

namespace Avax\HTTP\Security;


use Avax\HTTP\Session\Session;
use Exception;
use Psr\Log\LoggerInterface;

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
        private Session         $session,
        private LoggerInterface $logger
    ) {}

    /**
     * Retrieves or generates a CSRF token tied to the session.
     *
     * @return string The CSRF token.
     * @throws Exception If the token generation process fails.
     */
    public function getToken() : string
    {
        $tokens = $this->getTokens();
        $tokens = $this->pruneExpiredTokens(tokens: $tokens);

        if (count($tokens) >= self::MAX_TOKENS_PER_SESSION) {
            $this->logger->warning(
                message: 'Maximum CSRF token limit reached.',
                context: ['tokens' => $tokens]
            );
        }

        $newToken          = $this->generateToken();
        $tokens[$newToken] = time();

        $this->storeTokens(tokens: $tokens);

        $this->logger->info(
            message: 'Generated new CSRF token.',
            context: ['token' => $newToken]
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

    private function storeTokens(array $tokens) : void
    {
        $this->session->put(key: self::SESSION_KEY, value: $tokens);
    }

    private function pruneExpiredTokens(array $tokens) : array
    {
        $currentTime = time();

        return array_filter(
            $tokens,
            static fn($timestamp) => $currentTime - $timestamp <= self::TOKEN_EXPIRATION_MINUTES * 60
        );
    }

    /**
     * @throws \Random\RandomException
     */
    private function generateToken() : string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * @throws \Random\RandomException
     */
    public function validateToken(string|null $token) : bool
    {
        $tokens = $this->getTokens();

        if ($token === null || ! isset($tokens[$token])) {
            $this->logger->warning(
                message: 'CSRF validation failed: Missing or invalid token.',
                context: ['token' => $token]
            );

            return false;
        }

        $isExpired = time() - $tokens[$token] > self::TOKEN_EXPIRATION_MINUTES * 60;

        if ($isExpired) {
            $this->logger->info(message: 'CSRF token expired.', context: ['token' => $token]);
            unset($tokens[$token]);
            $this->storeTokens(tokens: $tokens);

            return false;
        }

        unset($tokens[$token]);
        $newToken          = $this->generateToken();
        $tokens[$newToken] = time();
        $this->storeTokens(tokens: $tokens);

        $this->session->regenerate(); // Updated for Avax Session

        $this->logger->info(
            message: 'CSRF token validated and rotated.',
            context: ['new_token' => $newToken]
        );

        return true;
    }
}

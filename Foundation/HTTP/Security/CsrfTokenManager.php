<?php

declare(strict_types=1);

namespace Avax\HTTP\Security;

use Carbon\Carbon;
use Exception;
use Avax\HTTP\Session\Contracts\SessionInterface;
use Psr\Log\LoggerInterface;

/**
 * The `CsrfTokenManager` is a high-level component that manages CSRF tokens
 * to prevent cross-site request forgery attacks. It ensures secure
 * communication between the client and server by generating, validating,
 * expiring, and revoking tokens.
 */
final readonly class CsrfTokenManager
{
    /**
     * The session key under which all CSRF tokens are stored.
     *
     * @var string
     */
    private const string SESSION_KEY = '_csrf_tokens';

    /**
     * The number of minutes before a CSRF token expires.
     *
     * @var int
     */
    private const int TOKEN_EXPIRATION_MINUTES = 30;

    /**
     * The maximum number of tokens allowed per session to prevent
     * unbounded growth of session data.
     *
     * @var int
     */
    private const int MAX_TOKENS_PER_SESSION = 5;

    /**
     * Constructor with constructor promotion for simple, expressive instantiation.
     *
     * @param SessionInterface $session The session management implementation.
     * @param LoggerInterface  $logger  Responsible for logging important events.
     */
    public function __construct(
        private SessionInterface $session,
        private LoggerInterface  $logger
    ) {}

    /**
     * Retrieves or generates a CSRF token tied to the session. De-duplicates
     * tokens per session, handles expiration, and warns if maximum token count
     * is exceeded.
     *
     * @return string The CSRF token, valid for secure use in client-server interaction.
     * @throws Exception If the token generation process fails.
     */
    public function getToken() : string
    {
        // Retrieve current tokens from the session; fall back to an empty array if none exist.
        $tokens = $this->getTokens();

        // Prune expired tokens based on expiration policy to maintain session hygiene.
        $tokens = $this->pruneExpiredTokens(tokens: $tokens);

        // Log a warning if the number of tokens exceeds the predefined limit.
        if (count(value: $tokens) >= self::MAX_TOKENS_PER_SESSION) {
            $this->logger->warning(
                message: 'Maximum CSRF token limit reached.',
                context: ['tokens' => $tokens]
            );
        }

        // Generate a new cryptographically secure CSRF token.
        $newToken = $this->generateToken();

        // Store the token along with the current timestamp for expiration management.
        $tokens[$newToken] = Carbon::now()->timestamp;

        // Persist the updated token array back to the session.
        $this->storeTokens(tokens: $tokens);

        // Log the successful creation of the new token.
        $this->logger->info(
            message: 'Generated new CSRF token.',
            context: ['token' => $newToken]
        );

        // Return the newly generated token to the caller.
        return $newToken;
    }

    /**
     * Fetches all existing CSRF tokens from the session.
     * If the session value is invalid, it resets to an empty array to ensure continuity.
     *
     * @return array The stored tokens, keyed by token string with timestamp as value.
     */
    private function getTokens() : array
    {
        // Retrieve tokens from the session or use an empty array as the default value.
        $tokens = $this->session->get(key: self::SESSION_KEY, default: []);

        // Handle cases where the session value is corrupted or in an invalid format.
        if (! is_array(value: $tokens)) {
            $this->logger->warning(
                message: 'CSRF tokens session value was not an array. Resetting.',
                context: ['type' => gettype(value: $tokens)]
            );

            // Reset tokens to an empty array if invalid data is found.
            $this->storeTokens(tokens: []);

            return [];
        }

        return $tokens;
    }

    /**
     * Stores the provided token array into the session under the preconfigured key.
     *
     * @param array $tokens The array of tokens to store in session.
     */
    private function storeTokens(array $tokens) : void
    {
        // Set the tokens into the session storage under the configured key.
        $this->session->set(key: self::SESSION_KEY, value: $tokens);
    }

    /**
     * Filters out expired tokens from the provided token list under the configured
     * expiration policy. Ensures tokens are valid for a limited time window.
     *
     * @param array $tokens The array of tokens to validate and prune.
     *
     * @return array The pruned token array containing only valid tokens.
     */
    private function pruneExpiredTokens(array $tokens) : array
    {
        // Get the current timestamp for comparison.
        $currentTime = Carbon::now()->timestamp;

        // Filter out tokens that have exceeded their expiration time.
        return array_filter(
            array   : $tokens,
            callback: static fn($timestamp) => $currentTime - $timestamp <= self::TOKEN_EXPIRATION_MINUTES * 60
        );
    }

    /**
     * Generates a cryptographically secure random CSRF token.
     *
     * @return string The 32-byte token, encoded as a hexadecimal string.
     * @throws Exception If an internal error occurs during token generation.
     */
    private function generateToken() : string
    {
        // Use a cryptographic function to generate a secure 32-byte token.
        return bin2hex(string: random_bytes(length: 32));
    }

    /**
     * Validates a given client-provided CSRF token against the session's stored
     * tokens. Handles expired tokens, token invalidation, and token rotation for
     * enhanced security.
     *
     * @param string|null $token The token provided by the client for validation.
     *
     * @return bool Returns true if the token is valid and rotated; false otherwise.
     * @throws Exception If token generation or session operations fail unexpectedly.
     */
    public function validateToken(string|null $token) : bool
    {
        // Retrieve the session’s stored tokens for comparison.
        $tokens = $this->getTokens();

        // Validation fails if the token is missing or unrecognized.
        if ($token === null || ! isset($tokens[$token])) {
            $this->logger->warning(
                message: 'CSRF validation failed: Missing or invalid token.',
                context: ['token' => $token]
            );

            return false;
        }

        // Determine if the token has exceeded the expiration window.
        $isExpired = Carbon::now()->timestamp - $tokens[$token] > self::TOKEN_EXPIRATION_MINUTES * 60;

        // If the token is expired, remove it and prevent usage.
        if ($isExpired) {
            $this->logger->info(message: 'CSRF token expired.', context: ['token' => $token]);
            unset($tokens[$token]);
            $this->storeTokens(tokens: $tokens);

            return false;
        }

        // Rotate tokens for added security: remove the old token and generate a new one.
        unset($tokens[$token]);
        $newToken = $this->generateToken();
        $tokens[$newToken] = Carbon::now()->timestamp;
        $this->storeTokens(tokens: $tokens);

        // Regenerate the session ID to prevent fixation attacks.
        $this->session->regenerateId();

        // Log the successful token validation and rotation.
        $this->logger->info(
            message: 'CSRF token validated and rotated.',
            context: ['new_token' => $newToken]
        );

        return true;
    }

    /**
     * Invalidates all CSRF tokens in the current session scope.
     * Also regenerates the session ID to further enhance security.
     */
    public function invalidateAllTokens() : void
    {
        // Completely remove the CSRF tokens from the session.
        $this->session->delete(key: self::SESSION_KEY);

        // Regenerate the session ID to prevent session fixation or hijacking attacks.
        $this->session->regenerateId();

        // Log that all tokens have been invalidated.
        $this->logger->info(message: 'All CSRF tokens invalidated.');
    }
}

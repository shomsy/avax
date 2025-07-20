<?php

declare(strict_types=1);

namespace Gemini\Auth\Application\Action;

use Gemini\Auth\Application\Service\AuthenticationService;
use Gemini\HTTP\Security\CsrfTokenManager;
use Psr\Http\Message\ResponseInterface;

/**
 * LogoutAction handles user logout requests by properly terminating sessions and generating a new CSRF token.
 */
final readonly class LogoutAction
{
    public function __construct(
        private AuthenticationService $authenticationService,
        private CsrfTokenManager      $csrfTokenManager
    ) {}

    /**
     * Handles the logout process.
     *
     * - Terminates the authenticated session.
     * - Clears session data and regenerates session ID.
     * - Invalidates and regenerates the CSRF token for security.
     * - Returns a standardized response.
     *
     * @return ResponseInterface Response indicating successful logout.
     * @throws \Exception
     */
    public function logout() : ResponseInterface
    {
        // Step 1: Log out the user
        $this->authenticationService->logout();

        // Step 2: Invalidate all CSRF tokens and generate a new one
        $this->csrfTokenManager->invalidateAllTokens();
        $newToken = $this->csrfTokenManager->getToken();

        // Step 3: Return a response indicating successful logout
        return response()->send(
            data: [
                      'status'     => 'success',
                      'message'    => 'Logged out successfully.',
                      'csrf_token' => $newToken,
                  ]
        );
    }
}

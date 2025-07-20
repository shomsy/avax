<?php

declare(strict_types=1);

namespace Gemini\Auth\Application\UseCase\API;

use Psr\Http\Message\ResponseInterface;

/**
 * Class ApiLogoutUseCase
 * This class handles the user logout operation.
 *
 * This class uses the auth() helper to access authentication services and handle
 * the logout process. It ensures a response is returned indicating the success of the operation.
 */
final class ApiLogoutUseCase
{
    /**
     * Executes the logout operation.
     *
     * This method will log out the authenticated user by calling the logout method
     * on the authentication service. After logging the user out, it returns a
     * response with a success message.
     *
     * @return ResponseInterface Returns a success logout response.
     * @throws \Exception If any error occurs during the logout process.
     */
    public function execute() : ResponseInterface
    {
        // Perform the logout operation via the auth helper.
        auth()->logout();

        // Return a standardized response indicating the logout was successful.
        return response()->send(
            data: [
                      'status'  => 'success',
                      'message' => 'Logged out successfully.',
                  ],
        );
    }
}

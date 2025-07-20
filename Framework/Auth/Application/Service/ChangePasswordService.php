<?php

declare(strict_types=1);

namespace Gemini\Auth\Application\Service;

use Gemini\Auth\Contracts\Identity\Subject\UserInterface;
use Gemini\Auth\Contracts\Identity\UserSourceInterface;
use Gemini\HTTP\Request\Request;
use Psr\Http\Message\ResponseInterface;

/**
 * Service responsible for handling password changes.
 */
final readonly class ChangePasswordService
{
    public function __construct(
        private AuthenticationService $authenticationService,
        private UserSourceInterface   $userProvider,
        private PasswordHasher        $passwordHasher
    ) {}

    /**
     * Handles the password change request.
     *
     * ## Technical Description
     * - Retrieves the currently authenticated user.
     * - Validates the current password.
     * - Hashes and updates the new password.
     * - Returns an appropriate response.
     *
     * ## Business Description
     * - Ensures secure password changes.
     * - Enforces validation rules.
     * - Prevents unauthorized password modifications.
     *
     * @param Request $request The HTTP request containing old and new passwords.
     *
     * @return ResponseInterface Response indicating success or failure.
     */
    public function changePassword(Request $request) : ResponseInterface
    {
        // Retrieve authenticated user
        $user = $this->authenticationService->user();
        if (! $user instanceof UserInterface) {
            return $this->unauthorizedResponse();
        }

        // Validate input
        $currentPassword = $request->get(key: 'current_password');
        $newPassword     = $request->get(key: 'new_password');
        $confirmPassword = $request->get(key: 'confirm_password');

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            return $this->validationErrorResponse(message: 'All password fields are required.');
        }

        // Check if the current password is valid
        if (! $this->passwordHasher->verify($currentPassword, $user->getPassword())) {
            return $this->validationErrorResponse(message: 'Current password is incorrect.');
        }

        // Ensure a new password and confirmation match
        if ($newPassword !== $confirmPassword) {
            return $this->validationErrorResponse(message: 'New password and confirmation do not match.');
        }

        // Hash and update new password
        $hashedPassword = $this->passwordHasher->hash($newPassword);
        $this->userProvider->updatePassword($user->getId(), $hashedPassword);

        return $this->successResponse();
    }

    /**
     * Returns an unauthorized response.
     */
    private function unauthorizedResponse() : ResponseInterface
    {
        return response()->send(
            data  : ['message' => 'Unauthorized'],
            status: 401
        );
    }

    /**
     * Returns a validation error response.
     */
    private function validationErrorResponse(string $message) : ResponseInterface
    {
        return response()->send(
            data  : ['error' => $message],
            status: 422
        );
    }

    /**
     * Returns a success response indicating password change success.
     */
    private function successResponse() : ResponseInterface
    {
        return response()->send(data: ['success' => 'Password changed successfully.']);
    }
}

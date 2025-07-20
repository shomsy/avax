<?php

declare(strict_types=1);

namespace Gemini\Auth\Application\UseCase\API;

use Gemini\Auth\Contracts\Identity\UserSourceInterface;
use Gemini\Auth\DTO\RegistrationDTO;
use Gemini\HTTP\Request\Request;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ApiRegisterUseCase
 * Handles user registration process.
 */
final class ApiRegisterUseCase
{
    /**
     * Executes the user registration process.
     *
     * @param Request $request The HTTP request containing the user registration data.
     *
     * @return ResponseInterface The HTTP response indicating registration success or failure.
     * @throws \Exception If any part of the registration process fails.
     */
    public function execute(Request $request) : ResponseInterface
    {
        // Extract registration data from the request and populate RegistrationDTO.
        $registrationDTO = new RegistrationDTO(
            data: [
                      'email'    => $request->get(key: 'email'),
                      'username' => $request->get(key: 'username'),
                      'password' => $request->get(key: 'password'),
                  ],
        );

        // Create a new user using the UserSourceInterface service.
        $user = app(abstract: UserSourceInterface::class)->createUser($registrationDTO);

        // Return a JSON response with the newly created user details.
        return response()->send(
            data  : [
                        'status' => 'success',
                        'user'   => [
                            'id'       => $user->getId(),
                            'email'    => $user->getEmail(),
                            'username' => $user->getUsername(),
                        ],
                    ],
            status: 201,
        );
    }
}

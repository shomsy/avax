<?php

declare(strict_types=1);

namespace Gemini\Auth\Application\Action;

use Gemini\Auth\Contracts\Identity\UserSourceInterface;
use Gemini\Auth\DTO\RegistrationDTO;
use Gemini\HTTP\Request\Request;
use Psr\Http\Message\ResponseInterface;

/**
 * Handles user registration logic.
 */
final readonly class RegisterUserAction
{
    public function __construct(private UserSourceInterface $userProvider) {}

    /**
     * Handles the user registration process.
     *
     * @param Request $request The HTTP request containing registration data.
     *
     * @return ResponseInterface The response indicating the registration result.
     * @throws \ReflectionException
     */
    public function register(Request $request) : ResponseInterface
    {
        // Validate user request and create DTO
        $validatedDataFromRequest = new RegistrationDTO(
            data: [
                      'first_name' => $request->get(key: 'first_name'),
                      'last_name'  => $request->get(key: 'last_name'),
                      'email'      => $request->get(key: 'email'),
                      'username'   => $request->get(key: 'username'),
                      'is_admin'   => $request->get(key: 'is_admin', default: false),
                      'password'   => $request->get(key: 'password'),
                  ]
        );

        // Create user via Subject
        $user = $this->userProvider->createUser(registrationDTO: $validatedDataFromRequest);

        // Return response
        return response()->send(
            data  : [
                        'status' => 'success',
                        'user'   => [
                            'id'       => $user->getId(),
                            'email'    => $user->getEmail(),
                            'username' => $user->getUsername(),
                        ],
                    ],
            status: 201
        );
    }
}

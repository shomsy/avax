<?php

declare(strict_types=1);

namespace Avax\Auth\Http\Controllers;

use Avax\Auth\Actions\Register;
use Avax\Auth\Data\RegistrationDTO;
use Avax\Exceptions\ValidationException;
use Avax\HTTP\Request\Request;
use Psr\Http\Message\ResponseInterface;

final readonly class RegisterController
{
    public function __construct(private Register $registerAction) {}

    public function register(Request $request): ResponseInterface
    {
        try {
            $data = new RegistrationDTO(data: $request->allInputs());
            $user = $this->registerAction->execute(data: $data);

            return response()->send(
                data: [
                    'status' => 'success',
                    'user' => [
                        'id' => $user->getId(),
                        'email' => $user->getEmail(),
                        'username' => $user->getUsername(),
                    ]
                ],
                status: 201
            );
        } catch (ValidationException $e) {
            return response()->send(data: ['error' => 'Validation failed', 'details' => $e->getErrors()], status: 422);
        } catch (\Throwable $e) {
            return response()->send(data: ['error' => 'Registration failed', 'message' => $e->getMessage()], status: 500);
        }
    }
}

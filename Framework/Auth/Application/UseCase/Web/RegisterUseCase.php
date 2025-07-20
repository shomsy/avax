<?php

declare(strict_types=1);

namespace Gemini\Auth\Application\UseCases\Web;

use Gemini\Auth\Application\Service\RegisterUserAction;
use Gemini\HTTP\Request\Request;
use Psr\Http\Message\ResponseInterface;

/**
 * Handles user registration through the `RegisterUserAction`.
 *
 * Technical Description:
 * This class serves as a use case for processing user registration requests.
 * It delegates the actual registration logic to the `RegisterUserAction` class.
 *
 * Business Description:
 * This use case facilitates the registration of new users,
 * enabling them to create accounts and access platform features.
 */
final readonly class RegisterUseCase
{
    public function __construct(private RegisterUserAction $registerService) {}

    /**
     * @param Request $request The HTTP request containing user registration data.
     *
     * @return ResponseInterface The response that contains the outcome of the registration process.
     */
    public function execute(Request $request) : ResponseInterface
    {
        return $this->registerService->register($request);
    }
}
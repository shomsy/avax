<?php

declare(strict_types=1);

namespace Gemini\Auth\Application\Action;


use Gemini\Auth\Application\Service\AuthenticationService;
use Gemini\Auth\Application\Service\RateLimiterService;
use Gemini\Auth\Domain\Exception\AuthenticationException;
use Gemini\Auth\Domain\ValueObject\Credentials;
use Gemini\Auth\DTO\AuthenticationDTO;
use Gemini\Exceptions\ValidationException;
use Gemini\HTTP\Request\Request;
use Psr\Http\Message\ResponseInterface;

/**
 * LoginAction handles user login requests, combining validation, authentication, and response generation.
 */
final readonly class LoginAction
{
    /**
     * @param RateLimiterService    $rateLimiter           Action to prevent brute-force attacks.
     * @param AuthenticationService $authenticationService Action handling authentication logic.
     */
    public function __construct(
        private RateLimiterService    $rateLimiter,
        private AuthenticationService $authenticationService
    ) {}

    /**
     * Handles the processing of a login request.
     *
     * @param Request $request The HTTP request containing user login data.
     *
     * @return ResponseInterface A response indicating the login result.
     * @throws AuthenticationException|\ReflectionException
     */
    public function login(Request $request) : ResponseInterface
    {
        try {
            // Validate input using AuthenticationDTO
            $authenticationDTO = new AuthenticationDTO(data: $request->allInputs());
            $credentials       = new Credentials(authenticationDTO: $authenticationDTO);

            // Rate-limiting check. Brute force prevention.
            $identifier = $credentials->getIdentifierValue();
            if (! $this->rateLimiter->canAttempt(identifier: $identifier)) {
                return response()->send(data: ['error' => 'Too many login attempts'], status: 429);
            }

            // Authenticate user
            if (! $user = $this->authenticationService->login(credentials: $credentials)) {
                throw new ValidationException(message: 'Invalid credentials');
            }

            // Reset the rate limiter on success
            $this->rateLimiter->resetAttempts(identifier: $identifier);

            return response()->send(data: ['success' => 'Logged in', 'user' => $user]);
        } catch (ValidationException $e) {
            return response()->send(
                data  : ['error' => 'Validation failed', 'details' => $e->getErrors()],
                status: 422
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace Gemini\Auth\Http\Controllers;

use Gemini\Auth\Authenticator;
use Gemini\Auth\Data\Credentials;
use Gemini\Auth\Data\RegistrationData; // Unused here, but ensuring namespace visibility
use Gemini\Auth\Adapters\RateLimiter;
use Gemini\Auth\Exceptions\AuthFailed;
use Gemini\Exceptions\ValidationException;
use Gemini\HTTP\Request\Request;
use Psr\Http\Message\ResponseInterface;

final readonly class LoginController
{
    public function __construct(
        private RateLimiter   $rateLimiter,
        private Authenticator $authenticator
    ) {}

    public function login(Request $request): ResponseInterface
    {
        try {
            // Create Credentials DTO (Wait, DTO usually validates on construct or explicitly?)
            // Assuming Validation runs on object creation via Attributes
            $credentials = new Credentials(data: $request->allInputs()); // AbstractDTO usually takes array

            // Rate-limiting
            $identifier = $credentials->getIdentifierValue();
            if (! $this->rateLimiter->canAttempt(identifier: $identifier)) {
               return response()->send(data: ['error' => 'Too many login attempts'], status: 429);
            }

            // Authenticate
            $user = $this->authenticator->login($credentials);

            // Reset attempts
            $this->rateLimiter->resetAttempts(identifier: $identifier);

            return response()->send(data: ['success' => 'Logged in', 'user' => $user]);

        } catch (AuthFailed $e) {
             return response()->send(data: ['error' => 'Invalid credentials'], status: 401);
        } catch (ValidationException $e) {
             return response()->send(data: ['error' => 'Validation failed', 'details' => $e->getErrors()], status: 422);
        } catch (\Throwable $e) {
             // Fallback
             return response()->send(data: ['error' => 'Login failed', 'message' => $e->getMessage()], status: 500);
        }
    }
}

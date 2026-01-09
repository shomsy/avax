<?php

declare(strict_types=1);

namespace Avax\Auth\Http\Controllers;

use Avax\Auth\Adapters\RateLimiter;
use Avax\Auth\Authenticator;
use Avax\Auth\Data\Credentials;
use Avax\Auth\Exceptions\AuthFailed;
use Avax\Exceptions\ValidationException;
use Avax\HTTP\Request\Request;
use Psr\Http\Message\ResponseInterface;
use Throwable;

// Unused here, but ensuring namespace visibility

final readonly class LoginController
{
    public function __construct(
        private RateLimiter   $rateLimiter,
        private Authenticator $authenticator
    ) {}

    public function login(Request $request) : ResponseInterface
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
            $user = $this->authenticator->login(credentials: $credentials);

            // Reset attempts
            $this->rateLimiter->resetAttempts(identifier: $identifier);

            return response()->send(data: ['success' => 'Logged in', 'user' => $user]);

        } catch (AuthFailed $e) {
            return response()->send(data: ['error' => 'Invalid credentials'], status: 401);
        } catch (ValidationException $e) {
            return response()->send(data: ['error' => 'Validation failed', 'details' => $e->getErrors()], status: 422);
        } catch (Throwable $e) {
            // Fallback
            return response()->send(data: ['error' => 'Login failed', 'message' => $e->getMessage()], status: 500);
        }
    }
}

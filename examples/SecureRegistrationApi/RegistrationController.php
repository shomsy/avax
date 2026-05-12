<?php

declare(strict_types=1);

namespace Avax\Examples\SecureRegistrationApi;

use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes\OnFailure;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes\ReportFailure;

/**
 * RegistrationController — Real reference flow adopting FailureBoundary attributes.
 *
 * This controller proves OnFailure and ReportFailure are used beyond the demo controller:
 * - Maps ValidationFailed to 422 Unprocessable Entity
 * - Maps RegistrationFailed to 409 Conflict
 * - Maps ExternalServiceDown to 503 Service Unavailable
 * - Reports all failures through the observability pipeline
 *
 * @see https://avax.test/v5.6-y6-real-adoption
 */
final readonly class RegistrationController
{
    /**
     * Register a new user.
     *
     * @throws ValidationFailed
     * @throws RegistrationFailed
     */
    #[OnFailure(ValidationFailed::class, respondWith: 422, messageKey: 'validation_failed')]
    #[OnFailure(RegistrationFailed::class, respondWith: 409, messageKey: 'registration_failed')]
    #[ReportFailure(channel: 'http')]
    public function register(array $input): ResponseInterface
    {
        if (empty($input['email'] ?? '')) {
            throw new ValidationFailed('Email is required');
        }

        if (empty($input['password'] ?? '')) {
            throw new ValidationFailed('Password is required');
        }

        if (strlen($input['password']) < 8) {
            throw new ValidationFailed('Password must be at least 8 characters');
        }

        if (! filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ValidationFailed('Email format is invalid');
        }

        return Response::json([
            'status' => 'registered',
            'message' => 'User registered successfully',
        ]);
    }

    /**
     * Simulate external service dependency failure.
     *
     * @throws ExternalServiceDown
     */
    #[OnFailure(ExternalServiceDown::class, respondWith: 503, messageKey: 'external_service_down')]
    #[ReportFailure(channel: 'http')]
    public function verifyIdentity(string $provider): ResponseInterface
    {
        if ($provider === 'unavailable') {
            throw new ExternalServiceDown('Identity provider is unavailable');
        }

        return Response::json([
            'status' => 'verified',
            'provider' => $provider,
        ]);
    }
}

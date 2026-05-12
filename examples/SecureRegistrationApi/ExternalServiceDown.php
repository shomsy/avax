<?php

declare(strict_types=1);

namespace Avax\Examples\SecureRegistrationApi;

use RuntimeException;

/**
 * ExternalServiceDown — Thrown when an external identity provider is unavailable.
 *
 * Mapped to 503 Service Unavailable via #[OnFailure] attribute.
 */
final class ExternalServiceDown extends RuntimeException
{
}

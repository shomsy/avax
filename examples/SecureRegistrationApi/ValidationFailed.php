<?php

declare(strict_types=1);

namespace Avax\Examples\SecureRegistrationApi;

use RuntimeException;

/**
 * ValidationFailed — Thrown when registration input fails validation.
 *
 * Mapped to 422 Unprocessable Entity via #[OnFailure] attribute.
 */
final class ValidationFailed extends RuntimeException
{
}

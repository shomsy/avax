<?php

declare(strict_types=1);

namespace Avax\Examples\SecureRegistrationApi;

use RuntimeException;

/**
 * RegistrationFailed — Thrown when registration fails due to business rules.
 *
 * Mapped to 409 Conflict via #[OnFailure] attribute.
 */
final class RegistrationFailed extends RuntimeException
{
}

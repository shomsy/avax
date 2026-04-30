<?php

declare(strict_types=1);

namespace Avax\Components\Application\Validation\System\Capabilities\Standard\Email;

use Avax\Components\Application\Text\Pattern;
use SensitiveParameter;

/**
 * Action Owner: Validates email format according to standard patterns.
 *
 * Part of the Foundation\Validation capability.
 */
final readonly class ValidateEmail
{
    private const string EMAIL_PATTERN = '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$';

    /**
     * Performs strict validation of the given email string.
     *
     * @param string $email The email address to validate.
     *
     * @return bool True if the email is valid.
     */
    public function execute(#[SensitiveParameter] string $email) : bool
    {
        if ($email === '') {
            return false;
        }

        // We use the Pattern component for consistent regex handling
        return Pattern::of(raw: self::EMAIL_PATTERN)->test(subject: $email);
    }
}

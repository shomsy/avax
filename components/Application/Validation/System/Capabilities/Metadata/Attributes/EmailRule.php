<?php

declare(strict_types=1);

namespace Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes;

use Attribute;
use InvalidArgumentException;

/**
 * Email Validation Rule - RFC 5321 compliant with DNS option
 *
 * Enterprise-grade email validation with:
 * - RFC-compliant pattern
 * - Optional DNS MX record check
 * - Customizable error messages
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class EmailRule
{
    private const RFC5321_PATTERN = '/^[a-zA-Z0-9.!#$%&\x27*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])+)*$/';

    public function __construct(
        private bool   $requireDnsValidation = false,
        private string $message = 'Field "{property}" must be a valid email address.',
    ) {}

    /**
     * @throws InvalidArgumentException if email is invalid
     */
    public function validate(mixed $value, string $property) : void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value)) {
            throw new InvalidArgumentException(
                str_replace('{property}', $property, $this->message)
            );
        }

        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                str_replace('{property}', $property, $this->message)
            );
        }

        if ($this->requireDnsValidation) {
            $domain = substr($value, strpos($value, '@') + 1);
            if ($domain && ! checkdnsrr($domain, 'MX') && ! checkdnsrr($domain, 'A')) {
                throw new InvalidArgumentException(
                    "Field \"{$property}\" email domain does not exist."
                );
            }
        }
    }
}
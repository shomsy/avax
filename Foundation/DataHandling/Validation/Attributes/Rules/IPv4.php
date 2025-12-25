<?php

declare(strict_types=1);

/**
 * Attribute class to enforce IPv4 validation on properties.
 *
 * This class will be used to annotate properties and ensure they contain valid IPv4 addresses.
 * The main decision here is to leverage PHP's filter_var function with the FILTER_VALIDATE_IP flag.
 * This is a better approach than regular expressions due to its efficiency and robustness.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * An attribute class to validate if a given value is a valid IPv4 address.
 *
 * The validation is essential for ensuring that the property this attribute
 * is applied to adheres to the IPv4 format, which can be critical for
 * network configurations and communication protocols.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class IPv4
{
    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if (! filter_var(value: $value, filter: FILTER_VALIDATE_IP, options: FILTER_FLAG_IPV4)) {
            throw new ValidationException(message: $property . ' must be a valid IPv4 address.');
        }
    }
}

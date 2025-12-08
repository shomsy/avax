<?php

declare(strict_types=1);

namespace Avax\HTTP\URI\Components;

use InvalidArgumentException;
use Stringable;

/**
 * Represents a URI scheme (e.g., http, https, file).
 *
 * A final class to ensure immutability and prevent extension, which is crucial for maintaining
 * the integrity of specific URI schemes throughout the application.
 */
final readonly class Scheme implements Stringable
{
    // Allowed URI schemes. These are necessary for validating the input scheme to ensure it conforms
    // to the expected set of schemes, avoiding erroneous or unsupported inputs.
    private const array ALLOWED_SCHEMES = ['http', 'https', 'file', 'ftp', 'ws', 'wss', 'mailto', 'data'];

    // Holds the normalized scheme value, ensuring case insensitivity by storing in lowercase.
    private string $scheme;

    /**
     * Constructs a new Scheme instance.
     *
     * @param string $scheme The scheme to be validated and stored.
     *
     * Validates the provided scheme upon instantiation to ensure immediate feedback if the input
     * is not among the allowed schemes.
     */
    public function __construct(string $scheme)
    {
        $this->scheme = $this->validate(scheme: $scheme);
    }

    /**
     * Validates the provided scheme.
     *
     * @param string $scheme The scheme to validate.
     *
     * @return string The validated scheme in lowercase.
     *
     * @throws InvalidArgumentException if the scheme is empty or not in the allowed schemes list.
     */
    private function validate(string $scheme) : string
    {
        if ($scheme === '' || ! in_array(strtolower($scheme), self::ALLOWED_SCHEMES, true)) {
            throw new InvalidArgumentException(message: 'Invalid scheme: ' . $scheme);
        }

        return strtolower($scheme);
    }

    /**
     * Converts the Scheme object to a string.
     *
     * @return string The scheme as a string.
     *
     * Provides a string representation of the scheme, which is useful in contexts where the scheme
     * needs to be concatenated or outputted directly.
     */
    public function __toString() : string
    {
        return $this->scheme;
    }
}
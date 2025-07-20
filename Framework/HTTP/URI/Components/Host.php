<?php

declare(strict_types=1);

namespace Gemini\HTTP\URI\Components;

use InvalidArgumentException;
use Stringable;

/**
 * Represents a URI host (e.g., example.com).
 *
 * This final class ensures immutability and integrity of hostnames in the application,
 * preventing extension and ensuring validation is enforced upon instantiation.
 */
final readonly class Host implements Stringable
{
    /** @var string The validated and normalized host string. */
    private string $host;

    /**
     * Constructs a new Host instance.
     *
     * @param string $host The host string to be validated and stored.
     *
     * Ensures the host is validated upon instantiation to immediately catch any invalid inputs.
     */
    public function __construct(string $host)
    {
        $this->host = $this->validate(host: $host);
    }

    /**
     * Validates and normalizes the provided host.
     *
     * @param string $host The host to validate.
     *
     * @return string The validated and normalized host.
     *
     * @throws InvalidArgumentException If the host is empty or invalid.
     *
     * Performs validation checks, including Internationalized Domain Name (IDN) conversion, to
     * ensure the host is a valid domain. This process includes converting the host to ASCII using
     * UTS #46 and applying domain name validation rules.
     */
    private function validate(string $host) : string
    {
        if (trim($host) === '') {
            // Host cannot be an empty string.
            throw new InvalidArgumentException(message: "Host cannot be empty.");
        }

        $asciiHost = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

        // Ensures the host is a valid domain name after conversion to ASCII.
        if ($asciiHost === false || ! filter_var($asciiHost, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            throw new InvalidArgumentException(message: 'Invalid host: ' . $host);
        }

        // Return the host in lowercase to avoid case sensitivity issues.
        return strtolower($asciiHost);
    }

    /**
     * Converts the Host object to a string.
     *
     * @return string The validated and normalized host as a string.
     *
     * Provides a string representation of the host, useful for debugging and logging purposes.
     */
    public function __toString() : string
    {
        return $this->host;
    }
}
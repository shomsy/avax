<?php

/**
 * A value object representing a validated email address.
 *
 * Owns email normalization and validation rules so downstream code
 * never works with unverified email strings.
 */
final readonly class GoodValueObject
{
    public function __construct(
        private string $email,
    ) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email');
        }
    }

    /**
     * Returns the normalized lowercase version of the email.
     *
     * @throws \RuntimeException When the email has not been validated.
     */
    public function value(): string
    {
        return strtolower($this->email);
    }
}

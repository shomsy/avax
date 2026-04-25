<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Values\Money;

use Avax\DataFoundation\Exceptions\InvalidValueException;
use SensitiveParameter;
use Stringable;

/**
 * ISO-like three-letter currency code.
 */
final readonly class Currency implements Stringable
{
    private string $code;

    public function __construct(
        #[SensitiveParameter] string $code,
    )
    {
        $normalized = strtoupper(trim($code));

        if (! preg_match('/^[A-Z]{3}$/', $normalized)) {
            throw InvalidValueException::because(message: "Currency code must be three uppercase letters, got '{$code}'.");
        }

        $this->code = $normalized;
    }

    public function code() : string
    {
        return $this->code;
    }

    public function __toString() : string
    {
        return $this->code;
    }
}

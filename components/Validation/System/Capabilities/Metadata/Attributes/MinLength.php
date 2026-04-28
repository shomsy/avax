<?php

declare(strict_types=1);

namespace Avax\Components\Validation\System\Capabilities\Metadata\Attributes;

use Attribute;

/**
 * Metadata Attribute for minimum string length validation.
 *
 * Part of the Foundation\Validation capability.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class MinLength
{
    public function __construct(
        public int         $length,
        public string|null $message = null
    ) {}

    public function getMessage(string $property) : string
    {
        return $this->message ?? "Field {$property} must be at least {$this->length} characters long.";
    }
}

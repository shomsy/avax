<?php

declare(strict_types=1);

namespace Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes;

use Attribute;

/**
 * Metadata Attribute for minimum numeric value validation.
 *
 * Part of the Foundation\Validation capability.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Min
{
    public function __construct(
        public int|float   $minimum,
        public string|null $message = null
    ) {}

    public function getMessage(string $property) : string
    {
        return $this->message ?? "Field {$property} must be at least {$this->minimum}.";
    }
}

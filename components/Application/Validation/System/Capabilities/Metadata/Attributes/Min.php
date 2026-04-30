<?php

declare(strict_types=1);

namespace Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Min
{
    public function __construct(
        public int|float $minimum,
        public ?string $message = null,
    ) {}

    public function getMessage(string $property): string
    {
        return $this->message ?? sprintf('Field %s must be at least %s.', $property, $this->minimum);
    }
}

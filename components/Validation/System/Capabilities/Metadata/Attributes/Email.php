<?php

declare(strict_types=1);

namespace Avax\Components\Validation\System\Capabilities\Metadata\Attributes;

use Attribute;

/**
 * Metadata Attribute for Email validation.
 *
 * Part of the Foundation\Validation capability.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Email
{
    public function __construct(
        public string $message = 'Invalid email format'
    ) {}
}

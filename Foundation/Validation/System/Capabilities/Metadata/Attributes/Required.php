<?php

declare(strict_types=1);

namespace Avax\Validation\System\Capabilities\Metadata\Attributes;

use Attribute;

/**
 * Metadata Attribute for Required field validation.
 *
 * Part of the Foundation\Validation capability.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Required
{
    public function __construct(
        public string $message = 'This field is required'
    ) {}
}

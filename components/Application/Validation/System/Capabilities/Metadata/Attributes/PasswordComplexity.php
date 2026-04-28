<?php

declare(strict_types=1);

namespace Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes;

use Attribute;

/**
 * Metadata Attribute for password complexity validation.
 *
 * Part of the Foundation\Validation capability.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class PasswordComplexity
{
    public function __construct(
        public string $message = 'Password must contain uppercase, lowercase, and numeric characters'
    ) {}
}

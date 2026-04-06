<?php

declare(strict_types=1);

namespace Avax\Container\Features\Core\DTO;

use SensitiveParameter;

/**
 * Error Data Transfer Object
 *
 * Represents an error or failure result.
 * Used by guard policies and validation systems to communicate errors
 * without throwing exceptions.
 *
 * @see     docs/Features/Core/DTO/ErrorDTO.md
 */
final readonly class ErrorDTO
{
    public function __construct(
        public string                       $message,
        #[SensitiveParameter] public string $code = 'ERROR',
        public mixed                        $context = null
    ) {}
}

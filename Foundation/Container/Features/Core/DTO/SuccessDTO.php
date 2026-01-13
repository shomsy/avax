<?php

declare(strict_types=1);

namespace Avax\Container\Features\Core\DTO;

/**
 * Success Data Transfer Object
 *
 * Represents a successful operation result.
 * Used by guard policies and validation systems to indicate success.
 *
 * @see     docs/Features/Core/DTO/SuccessDTO.md
 */
final readonly class SuccessDTO
{
    public function __construct(
        public mixed  $data = null,
        public string $message = 'Success'
    ) {}
}

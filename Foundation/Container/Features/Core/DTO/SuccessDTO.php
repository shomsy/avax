<?php

declare(strict_types=1);
namespace Avax\Container\Features\Core\DTO;

/**
 * Standard enterprise-grade success result.
 */
final readonly class SuccessDTO
{
    /**
     * @param string $message Descriptive success message
     * @param mixed  $payload Optional operation result
     */
    public function __construct(
        public string $message,
        public mixed  $payload = null
    ) {}
}
<?php

declare(strict_types=1);
namespace Avax\Container\Features\Core\DTO;

use SensitiveParameter;

/**
 * Standard enterprise-grade error result.
 */
final readonly class ErrorDTO
{
    /**
     * @param string $message Safe, non-revealing error message
     * @param string $code    Machine-readable error code
     * @param array  $context Diagnostic context (logger-friendly)
     */
    public function __construct(
        public string                       $message,
        #[SensitiveParameter] public string $code = 'error',
        public array                        $context = []
    ) {}
}
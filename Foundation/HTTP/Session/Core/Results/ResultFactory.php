<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Core\Results;

/**
 * ResultFactory
 *
 * Factory for creating result objects.
 *
 * @package Avax\HTTP\Session\Core\Results
 */
final class ResultFactory
{
    /**
     * Create success result.
     *
     * @param mixed                $data    The result data.
     * @param array<string, mixed> $context Additional context.
     *
     * @return SuccessDTO
     */
    public static function success(mixed $data = null, array $context = []): SuccessDTO
    {
        return new SuccessDTO($data, $context);
    }

    /**
     * Create error result.
     *
     * @param string               $message The error message.
     * @param ResultStatus         $status  The result status.
     * @param array<string, mixed> $context Additional context.
     *
     * @return ErrorDTO
     */
    public static function error(
        string $message,
        ResultStatus $status = ResultStatus::FAILURE,
        array $context = []
    ): ErrorDTO {
        return new ErrorDTO($message, $status, $context);
    }

    /**
     * Create retryable error result.
     *
     * @param string               $message The error message.
     * @param array<string, mixed> $context Additional context.
     *
     * @return ErrorDTO
     */
    public static function retryable(string $message, array $context = []): ErrorDTO
    {
        return new ErrorDTO($message, ResultStatus::RETRYABLE, $context);
    }
}

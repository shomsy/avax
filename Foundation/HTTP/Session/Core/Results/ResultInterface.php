<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Core\Results;

/**
 * ResultInterface
 *
 * Contract for all action results.
 *
 * @package Avax\HTTP\Session\Core\Results
 */
interface ResultInterface
{
    /**
     * Check if result is success.
     *
     * @return bool
     */
    public function isSuccess(): bool;

    /**
     * Check if result is error.
     *
     * @return bool
     */
    public function isError(): bool;

    /**
     * Get result status.
     *
     * @return ResultStatus
     */
    public function getStatus(): ResultStatus;

    /**
     * Convert to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}

<?php

declare(strict_types=1);

namespace Avax\DataLayer\CommitDataChanges;

use Throwable;

/**
 * DetectDeadlock - recognizes deadlock and serialization failure signals for retry decisions.
 */
final readonly class DetectDeadlock
{
    public function detect(Throwable|string $failure) : bool
    {
        $message = strtolower(string: $failure instanceof Throwable ? $failure->getMessage() : $failure);

        return str_contains(haystack: $message, needle: 'deadlock')
            || str_contains(haystack: $message, needle: 'lock wait')
            || str_contains(haystack: $message, needle: '40001')
            || str_contains(haystack: $message, needle: '40p01');
    }
}

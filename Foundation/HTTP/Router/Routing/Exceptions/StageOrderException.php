<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing\Exceptions;

use RuntimeException;

final class StageOrderException extends RuntimeException
{
    /**
     * @param array<int,string> $pipeline
     * @param array<int,string> $expectedOrder
     */
    public static function duplicate(string $stage, array $pipeline, array $expectedOrder) : self
    {
        $message = sprintf(
            'Duplicate pipeline component detected: %s. Pipeline: [%s]. Expected order: %s.',
            $stage,
            implode(', ', $pipeline),
            implode(' -> ', $expectedOrder)
        );

        return new self(message: $message);
    }

    /**
     * @param array<int,string> $pipeline
     * @param array<int,string> $expectedOrder
     */
    public static function misordered(string $stage, array $pipeline, array $expectedOrder, string $reason = '') : self
    {
        $message = sprintf(
            'Pipeline component is misordered: %s. Pipeline: [%s]. Expected order: %s.',
            $stage,
            implode(', ', $pipeline),
            implode(' -> ', $expectedOrder)
        );

        if ($reason !== '') {
            $message .= " Reason: {$reason}.";
        }

        return new self(message: $message);
    }
}
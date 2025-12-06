<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Events;

/**
 * EventCorrelationTrait
 *
 * Trait for adding correlation IDs to events.
 *
 * @package Avax\HTTP\Session\Features\Events
 */
trait EventCorrelationTrait
{
    private static string|null $correlationId = null;

    /**
     * Get or generate correlation ID.
     *
     * @return string
     */
    protected function getCorrelationId(): string
    {
        if (self::$correlationId === null) {
            self::$correlationId = $this->generateCorrelationId();
        }

        return self::$correlationId;
    }

    /**
     * Generate new correlation ID.
     *
     * @return string
     */
    private function generateCorrelationId(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * Set correlation ID.
     *
     * @param string $id The correlation ID.
     *
     * @return void
     */
    public function setCorrelationId(string $id): void
    {
        self::$correlationId = $id;
    }
}

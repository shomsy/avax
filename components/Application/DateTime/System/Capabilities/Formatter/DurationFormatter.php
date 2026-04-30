<?php

declare(strict_types=1);

namespace Avax\Components\Application\DateTime\System\Capabilities\Formatter;

use DateInterval;

final class DurationFormatter
{
    public function format(DateInterval $dateInterval) : string
    {
        $parts = [];

        if ($dateInterval->y > 0) {
            $parts[] = $dateInterval->y . ' year' . ($dateInterval->y > 1 ? 's' : '');
        }

        if ($dateInterval->m > 0) {
            $parts[] = $dateInterval->m . ' month' . ($dateInterval->m > 1 ? 's' : '');
        }

        if ($dateInterval->d > 0) {
            $parts[] = $dateInterval->d . ' day' . ($dateInterval->d > 1 ? 's' : '');
        }

        if ($dateInterval->h > 0) {
            $parts[] = $dateInterval->h . ' hour' . ($dateInterval->h > 1 ? 's' : '');
        }

        if ($dateInterval->i > 0) {
            $parts[] = $dateInterval->i . ' minute' . ($dateInterval->i > 1 ? 's' : '');
        }

        if ($dateInterval->s > 0) {
            $parts[] = $dateInterval->s . ' second' . ($dateInterval->s > 1 ? 's' : '');
        }

        return empty($parts) ? '0 seconds' : implode(', ', $parts);
    }

    public function formatCompact(DateInterval $dateInterval) : string
    {
        $parts = [];

        if ($dateInterval->y > 0) {
            $parts[] = $dateInterval->y . 'y';
        }

        if ($dateInterval->m > 0) {
            $parts[] = $dateInterval->m . 'mo';
        }

        if ($dateInterval->d > 0) {
            $parts[] = $dateInterval->d . 'd';
        }

        if ($dateInterval->h > 0) {
            $parts[] = $dateInterval->h . 'h';
        }

        if ($dateInterval->i > 0) {
            $parts[] = $dateInterval->i . 'm';
        }

        if ($dateInterval->s > 0) {
            $parts[] = $dateInterval->s . 's';
        }

        return implode(' ', $parts);
    }

    public function formatSeconds(int $seconds) : string
    {
        if ($seconds < 60) {
            return $seconds . 's';
        }

        if ($seconds < 3600) {
            return floor($seconds / 60) . 'm ' . ($seconds % 60) . 's';
        }

        if ($seconds < 86400) {
            return floor($seconds / 3600) . 'h ' . floor(($seconds % 3600) / 60) . 'm';
        }

        return floor($seconds / 86400) . 'd ' . floor(($seconds % 86400) / 3600) . 'h';
    }

    public function formatForHumans(DateInterval $dateInterval) : string
    {
        $text = $this->format($dateInterval);

        return str_replace(
            ['year', 'month', 'day', 'hour', 'minute', 'second'],
            ['yr', 'mo', 'day', 'hr', 'min', 'sec'],
            $text,
        );
    }
}

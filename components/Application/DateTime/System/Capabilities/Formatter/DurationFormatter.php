<?php

declare(strict_types=1);

namespace Avax\Components\Application\DateTime\System\Capabilities\Formatter;

final class DurationFormatter
{
    public function format(\DateInterval $duration): string
    {
        $parts = [];

        if ($duration->y > 0) {
            $parts[] = $duration->y . ' year' . ($duration->y > 1 ? 's' : '');
        }

        if ($duration->m > 0) {
            $parts[] = $duration->m . ' month' . ($duration->m > 1 ? 's' : '');
        }

        if ($duration->d > 0) {
            $parts[] = $duration->d . ' day' . ($duration->d > 1 ? 's' : '');
        }

        if ($duration->h > 0) {
            $parts[] = $duration->h . ' hour' . ($duration->h > 1 ? 's' : '');
        }

        if ($duration->i > 0) {
            $parts[] = $duration->i . ' minute' . ($duration->i > 1 ? 's' : '');
        }

        if ($duration->s > 0) {
            $parts[] = $duration->s . ' second' . ($duration->s > 1 ? 's' : '');
        }

        return empty($parts) ? '0 seconds' : implode(', ', $parts);
    }

    public function formatCompact(\DateInterval $duration): string
    {
        $parts = [];

        if ($duration->y > 0) {
            $parts[] = $duration->y . 'y';
        }

        if ($duration->m > 0) {
            $parts[] = $duration->m . 'mo';
        }

        if ($duration->d > 0) {
            $parts[] = $duration->d . 'd';
        }

        if ($duration->h > 0) {
            $parts[] = $duration->h . 'h';
        }

        if ($duration->i > 0) {
            $parts[] = $duration->i . 'm';
        }

        if ($duration->s > 0) {
            $parts[] = $duration->s . 's';
        }

        return implode(' ', $parts);
    }

    public function formatSeconds(int $seconds): string
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

    public function formatForHumans(\DateInterval $duration): string
    {
        $text = $this->format($duration);
        
        return str_replace(['year', 'month', 'day', 'hour', 'minute', 'second'], 
                          ['yr', 'mo', 'day', 'hr', 'min', 'sec'], $text);
    }
}
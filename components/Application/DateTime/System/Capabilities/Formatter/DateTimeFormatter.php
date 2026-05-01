<?php

declare(strict_types=1);

namespace Avax\Components\Application\DateTime\System\Capabilities\Formatter;

use DateTimeInterface;
use DateTimeZone;

final class DateTimeFormatter
{
    private const array FORMAT_PRESETS
        = [
            'date'     => 'Y-m-d',
            'time'     => 'H:i:s',
            'datetime' => 'Y-m-d H:i:s',
            'timestamp' => 'U',
            'iso8601'  => 'c',
            'rfc2822'  => 'D, d M Y H:i:s O',
            'human'    => 'F j, Y g:i a',
            'short'    => 'm/d/Y',
            'file'     => 'Y-m-d_H-i-s',
    ];

    public function format(DateTimeInterface $datetime, string $format) : string
    {
        if (isset(self::FORMAT_PRESETS[$format])) {
            $format = self::FORMAT_PRESETS[$format];
        }

        return $datetime->format($format);
    }

    public function formatWithTimezone(DateTimeInterface $datetime, string $timezone) : string
    {
        $datetime = (clone $datetime)->setTimezone(new DateTimeZone($timezone));

        return $this->format($datetime, 'datetime');
    }

    public function formatDate(DateTimeInterface $datetime) : string
    {
        return $this->format($datetime, 'date');
    }

    public function formatTime(DateTimeInterface $datetime) : string
    {
        return $this->format($datetime, 'time');
    }

    public function formatDateTime(DateTimeInterface $datetime) : string
    {
        return $this->format($datetime, 'datetime');
    }

    public function formatIso8601(DateTimeInterface $datetime) : string
    {
        return $datetime->format(DateTimeInterface::ATOM);
    }

    public function formatRelative(DateTimeInterface $datetime, DateTimeInterface $now) : string
    {
        $diff = $datetime->getTimestamp() - $now->getTimestamp();

        if ($diff < 0) {
            return $this->formatPast(abs($diff));
        }

        if ($diff > 0) {
            return $this->formatFuture($diff);
        }

        return 'just now';
    }

    private function formatPast(int $seconds) : string
    {
        if ($seconds < 60) {
            return $seconds . ' seconds ago';
        }

        if ($seconds < 3600) {
            return floor($seconds / 60) . ' minutes ago';
        }

        if ($seconds < 86400) {
            return floor($seconds / 3600) . ' hours ago';
        }

        return floor($seconds / 86400) . ' days ago';
    }

    private function formatFuture(int $seconds) : string
    {
        if ($seconds < 60) {
            return 'in ' . $seconds . ' seconds';
        }

        if ($seconds < 3600) {
            return 'in ' . floor($seconds / 60) . ' minutes';
        }

        if ($seconds < 86400) {
            return 'in ' . floor($seconds / 3600) . ' hours';
        }

        return 'in ' . floor($seconds / 86400) . ' days';
    }
}

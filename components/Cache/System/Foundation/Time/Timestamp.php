<?php

declare(strict_types=1);

namespace components\Cache\System\Foundation\Time;

use Stringable;

final readonly class Timestamp implements Stringable
{
    public function __construct(
        public int $seconds,
        public int $nanoseconds = 0
    ) {}

    public static function now() : self
    {
        return new self(
            seconds    : (int) floor(microtime(true)),
            nanoseconds: (int) ((microtime(true) - floor(microtime(true))) * 1_000_000_000)
        );
    }

    public static function fromUnixTime(int $timestamp) : self
    {
        return new self(seconds: $timestamp);
    }

    public static function fromMilliseconds(int $milliseconds) : self
    {
        return new self(seconds: (int) floor($milliseconds / 1000));
    }

    public function toMilliseconds() : int
    {
        return ($this->seconds * 1000) + (int) floor($this->nanoseconds / 1_000_000);
    }

    public function add(Duration $duration) : self
    {
        $totalSeconds = $this->seconds + $duration->seconds;
        $totalNanos   = $this->nanoseconds + $duration->nanoseconds;

        if ($totalNanos >= 1_000_000_000) {
            $totalSeconds += (int) floor($totalNanos / 1_000_000_000);
            $totalNanos   = $totalNanos % 1_000_000_000;
        }

        return new self(seconds: $totalSeconds, nanoseconds: $totalNanos);
    }

    public function subtract(Duration $duration) : self
    {
        $totalSeconds = $this->seconds - $duration->seconds;
        $totalNanos   = $this->nanoseconds - $duration->nanoseconds;

        if ($totalNanos < 0) {
            $totalSeconds--;
            $totalNanos += 1_000_000_000;
        }

        if ($totalSeconds < 0) {
            return new self(seconds: 0, nanoseconds: 0);
        }

        return new self(seconds: $totalSeconds, nanoseconds: $totalNanos);
    }

    public function isAfter(self $other) : bool
    {
        return $this->seconds > $other->seconds
            || ($this->seconds === $other->seconds && $this->nanoseconds > $other->nanoseconds);
    }

    public function isBefore(self $other) : bool
    {
        return $this->seconds < $other->seconds
            || ($this->seconds === $other->seconds && $this->nanoseconds < $other->nanoseconds);
    }

    public function difference(self $other) : Duration
    {
        $diffSeconds = $this->seconds - $other->seconds;
        $diffNanos   = $this->nanoseconds - $other->nanoseconds;

        if ($diffNanos < 0) {
            $diffSeconds--;
            $diffNanos += 1_000_000_000;
        }

        if ($diffSeconds < 0) {
            $diffSeconds = 0;
            $diffNanos   = 0;
        }

        return new Duration(seconds: $diffSeconds, nanoseconds: $diffNanos);
    }

    public function __toString() : string
    {
        return (string) $this->toUnixTime();
    }

    public function toUnixTime() : int
    {
        return $this->seconds;
    }
}
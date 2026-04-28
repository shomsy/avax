<?php

declare(strict_types=1);

namespace Avax\DateTime\System\Capabilities\Duration;

final readonly class Duration
{
    private int $totalSeconds;

    private function __construct(
        int $seconds,
    )
    {
        $this->totalSeconds = $seconds;
    }

    public static function seconds(int $value) : self
    {
        return new self($value);
    }

    public static function minutes(int $value) : self
    {
        return new self($value * 60);
    }

    public static function hours(int $value) : self
    {
        return new self($value * 3600);
    }

    public static function days(int $value) : self
    {
        return new self($value * 86400);
    }

    public static function weeks(int $value) : self
    {
        return new self($value * 604800);
    }

    public static function months(int $value) : self
    {
        return new self($value * 2592000);
    }

    public static function years(int $value) : self
    {
        return new self($value * 31536000);
    }

    public static function fromUnit(int $value, DurationUnit $unit) : self
    {
        return new self($value * $unit->inSeconds());
    }

    public function inSeconds() : int
    {
        return $this->totalSeconds;
    }

    public function inMinutes() : float
    {
        return $this->totalSeconds / 60;
    }

    public function inHours() : float
    {
        return $this->totalSeconds / 3600;
    }

    public function inDays() : float
    {
        return $this->totalSeconds / 86400;
    }

    public function inWeeks() : float
    {
        return $this->totalSeconds / 604800;
    }

    public function add(Duration $other) : self
    {
        return new self($this->totalSeconds + $other->totalSeconds);
    }

    public function subtract(Duration $other) : self
    {
        return new self(max(0, $this->totalSeconds - $other->totalSeconds));
    }

    public function isZero() : bool
    {
        return $this->totalSeconds === 0;
    }

    public function isNegative() : bool
    {
        return $this->totalSeconds < 0;
    }
}
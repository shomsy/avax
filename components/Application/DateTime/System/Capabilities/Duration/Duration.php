<?php

declare(strict_types=1);

namespace Avax\Components\Application\DateTime\System\Capabilities\Duration;

final readonly class Duration
{
    private function __construct(private int $totalSeconds) {}

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

    public static function fromUnit(int $value, DurationUnit $durationUnit) : self
    {
        return new self($value * $durationUnit->inSeconds());
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

    public function add(Duration $duration) : self
    {
        return new self($this->totalSeconds + $duration->totalSeconds);
    }

    public function subtract(Duration $duration) : self
    {
        return new self(max(0, $this->totalSeconds - $duration->totalSeconds));
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

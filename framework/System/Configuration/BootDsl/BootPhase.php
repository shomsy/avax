<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\BootDsl;

/**
 * BootPhase — lifecycle stages for the Boot DSL engine.
 *
 * Each phase MUST complete before the next begins.
 * Phases are immutable once entered.
 */
enum BootPhase: string
{
    case Create   = 'create';
    case Register = 'register';
    case Compile  = 'compile';
    case Verify   = 'verify';
    case Boot     = 'boot';
    case Freeze   = 'freeze';
    case Run      = 'run';

    /**
     * Returns the next phase in the lifecycle.
     *
     * @throws \LogicException if this is the final phase.
     */
    public function next(): self
    {
        return match ($this) {
            self::Create   => self::Register,
            self::Register => self::Compile,
            self::Compile  => self::Verify,
            self::Verify   => self::Boot,
            self::Boot     => self::Freeze,
            self::Freeze   => self::Run,
            self::Run      => throw new \LogicException('Cannot advance past Run phase.'),
        };
    }

    /**
     * Returns true if this phase comes before or equals the given phase.
     */
    public function isBeforeOr(self $other): bool
    {
        return $this->order() <= $other->order();
    }

    private function order(): int
    {
        return match ($this) {
            self::Create   => 1,
            self::Register => 2,
            self::Compile  => 3,
            self::Verify   => 4,
            self::Boot     => 5,
            self::Freeze   => 6,
            self::Run      => 7,
        };
    }
}

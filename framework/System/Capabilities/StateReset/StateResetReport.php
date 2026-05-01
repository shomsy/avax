<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\StateReset;

use Throwable;

final readonly class StateResetReport
{
    /**
     * @param list<string>             $resetComponents
     * @param array<string, Throwable> $failures
     */
    public function __construct(
        private array $resetComponents,
        private array $failures = [],
    ) {}

    /**
     * @return list<string>
     */
    public function resetComponents() : array
    {
        return $this->resetComponents;
    }

    /**
     * @return array<string, Throwable>
     */
    public function failures() : array
    {
        return $this->failures;
    }

    public function wasSuccessful() : bool
    {
        return $this->failures === [];
    }
}

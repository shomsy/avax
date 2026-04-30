<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Access\RiskBasedAccess\Support;

/**
 * Deterministic outcome returned by the risk engine.
 */
final readonly class RiskDecision
{
    /**
     * @param list<string> $reasons
     */
    public function __construct(public RiskAction $action, public array $reasons = []) {}

    public static function allow(string ...$reasons) : self
    {
        return new self(action: RiskAction::ALLOW, reasons: array_values(array: $reasons));
    }
}

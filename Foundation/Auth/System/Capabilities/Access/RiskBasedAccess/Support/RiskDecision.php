<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Support;

/**
 * Deterministic outcome returned by the risk engine.
 */
final readonly class RiskDecision
{
    /** @var list<string> */
    public array      $reasons;
    public RiskAction $action;

    /**
     * @param list<string> $reasons
     */
    public function __construct(
        RiskAction $action,
        array      $reasons = []
    )
    {
        $this->action  = $action;
        $this->reasons = $reasons;
    }

    public static function allow(string ...$reasons) : self
    {
        return new self(action: RiskAction::ALLOW, reasons: array_values($reasons));
    }
}

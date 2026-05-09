<?php

declare(strict_types=1);

namespace Avax\Framework\System\Runtime\WarmApplication;

/**
 * WarmStateContract — Formal contract defining what stays warm and what must reset between requests.
 *
 * This contract is the single source of truth for warm worker safety classification.
 * It contains no request-specific runtime values. It is effectively immutable.
 */
final readonly class WarmStateContract
{
    /**
     * @var list<AllowedWarmState>
     */
    private array $allowedWarmState;

    /**
     * @var list<MustResetState>
     */
    private array $mustResetState;

    /**
     * @param list<AllowedWarmState>|null $allowedWarm
     * @param list<MustResetState>|null $mustReset
     */
    public function __construct(
        ?array $allowedWarm = null,
        ?array $mustReset = null,
    ) {
        $this->allowedWarmState = $allowedWarm ?? self::defaultAllowedWarm();
        $this->mustResetState = $mustReset ?? self::defaultMustReset();
    }

    /**
     * @return list<AllowedWarmState>
     */
    public function allowedWarmState(): array
    {
        return [...$this->allowedWarmState];
    }

    /**
     * @return list<MustResetState>
     */
    public function mustResetState(): array
    {
        return [...$this->mustResetState];
    }

    public function isAllowedWarm(AllowedWarmState $state): bool
    {
        return in_array($state, $this->allowedWarmState, true);
    }

    public function mustReset(MustResetState $state): bool
    {
        return in_array($state, $this->mustResetState, true);
    }

    /**
     * @return list<AllowedWarmState>
     */
    public static function defaultAllowedWarm(): array
    {
        return AllowedWarmState::cases();
    }

    /**
     * @return list<MustResetState>
     */
    public static function defaultMustReset(): array
    {
        return MustResetState::cases();
    }
}

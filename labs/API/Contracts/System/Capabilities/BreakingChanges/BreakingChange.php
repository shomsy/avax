<?php

declare(strict_types=1);

namespace Avax\Labs\API\Contracts\System\Capabilities\BreakingChanges;

final class BreakingChange
{
    public function __construct(
        public readonly BreakingChangeType $type,
        public readonly string             $path,
        public readonly string             $description,
        public readonly string|null        $before,
        public readonly string|null        $after,
    )
    {
    }

    public function severity(): string
    {
        return match ($this->type) {
            BreakingChangeType::REMOVED_ENDPOINT,
            BreakingChangeType::REMOVED_METHOD,
            BreakingChangeType::REMOVED_SUCCESS_RESPONSE => 'critical',
            BreakingChangeType::ADDED_REQUIRED_FIELD,
            BreakingChangeType::CHANGED_STATUS_CODE => 'major',
            default => 'minor',
        };
    }
}

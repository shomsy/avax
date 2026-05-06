<?php

declare(strict_types=1);

namespace Avax\Labs\API\DescribeApi\System\Capabilities\BreakingChanges;

final class BreakingChange
{
    public function __construct(
        public readonly BreakingChangeType $type,
        public readonly string $path,
        public readonly string $description,
        public readonly ?string $before,
        public readonly ?string $after,
    ) {
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

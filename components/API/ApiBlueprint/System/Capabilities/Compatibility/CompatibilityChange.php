<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Capabilities\Compatibility;

final class CompatibilityChange
{
    public function __construct(
        public readonly CompatibilityChangeType $type,
        public readonly string                  $path,
        public readonly string                  $description,
        public readonly ?string                 $before,
        public readonly ?string                 $after,
    ) {}

    public function severity() : string
    {
        return match ($this->type) {
            CompatibilityChangeType::REMOVED_ENDPOINT,
            CompatibilityChangeType::REMOVED_METHOD,
            CompatibilityChangeType::REMOVED_SUCCESS_RESPONSE => 'critical',
            CompatibilityChangeType::ADDED_REQUIRED_FIELD,
            CompatibilityChangeType::CHANGED_STATUS_CODE      => 'major',
            default                                           => 'minor',
        };
    }
}

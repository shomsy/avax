<?php

declare(strict_types=1);

namespace Avax\API\Contracts\System\Capabilities\BreakingChanges;

use ApiContract;

final readonly class BreakingChangeReport
{
    public function __construct(
        public bool  $hasBreakingChanges,
        public array $changes = [],
        public array $warnings = [],
    )
    {
    }
}

final readonly class BreakingChangeDetector
{
    public static function detect(ApiContract $old, ApiContract $new): BreakingChangeReport
    {
        $changes = [];

        if ($old->path() !== $new->path()) {
            $changes[] = [
                'type' => 'path_changed',
                'old' => $old->path(),
                'new' => $new->path(),
            ];
        }

        if ($old->method() !== $new->method()) {
            $changes[] = [
                'type' => 'method_changed',
                'old' => $old->method(),
                'new' => $new->method(),
            ];
        }

        if ($old->isDeprecated() !== $new->isDeprecated()) {
            $changes[] = [
                'type' => 'deprecation_changed',
                'old' => $old->isDeprecated(),
                'new' => $new->isDeprecated(),
            ];
        }

        return new BreakingChangeReport(
            hasBreakingChanges: (bool)$changes,
            changes: $changes,
        );
    }
}
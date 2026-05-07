<?php

declare(strict_types=1);

namespace Avax\Components\API\Contracts\System\Flows\DetectBreakingChange;

use Avax\Components\API\Contracts\System\Capabilities\BreakingChangeDetector\BreakingChangeDetector;

final readonly class DetectBreakingChange
{
    /**
     * @param array<string, mixed> $oldSchema
     * @param array<string, mixed> $newSchema
     *
     * @return array{has_breaking_changes:bool,breaking_changes:list<array{type:string,path:string,message:string,severity:string}>,total_changes:int}
     */
    public function execute(array $oldSchema, array $newSchema) : array
    {
        $detector = new BreakingChangeDetector();
        $changes  = $detector->detect(oldSchema: $oldSchema, newSchema: $newSchema);

        $breakingChanges = array_filter(
            array   : $changes,
            callback: static fn (array $change) : bool => $change['severity'] === 'breaking'
        );

        return [
            'has_breaking_changes' => ! empty($breakingChanges),
            'breaking_changes'     => array_values(array: $breakingChanges),
            'total_changes'        => count(value: $changes),
        ];
    }
}

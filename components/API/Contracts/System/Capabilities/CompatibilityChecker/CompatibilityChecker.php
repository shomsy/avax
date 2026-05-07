<?php

declare(strict_types=1);

namespace Avax\Components\API\Contracts\System\Capabilities\CompatibilityChecker;

use Avax\Components\API\Contracts\System\Capabilities\BreakingChangeDetector\BreakingChangeDetector;

final class CompatibilityChecker
{
    /**
     * @param array<string, mixed> $oldSchema
     * @param array<string, mixed> $newSchema
     *
     * @return array{compatible:bool,changes:list<array{type:string,path:string,message:string,severity:string}>}
     */
    public function check(array $oldSchema, array $newSchema) : array
    {
        $detector = new BreakingChangeDetector();
        $changes  = $detector->detect(oldSchema: $oldSchema, newSchema: $newSchema);

        return [
            'compatible' => empty($changes),
            'changes'    => $changes,
        ];
    }
}

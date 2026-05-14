<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\TestSupport\System\Flows\DetectBreakingChanges;

final readonly class DetectBreakingChanges
{
    /**
     * @param array<string, mixed> $oldContract
     * @param array<string, mixed> $newContract
     *
     * @return array{breaking: list<string>, compatible: bool}
     */
    public function detect(array $oldContract, array $newContract) : array
    {
        $breaking = [];

        foreach ($oldContract as $key => $value) {
            if (! isset($newContract[$key])) {
                $breaking[] = "Removed: {$key}";
            }
        }

        return ['breaking' => $breaking, 'compatible' => $breaking === []];
    }
}

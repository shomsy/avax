<?php

declare(strict_types=1);

namespace Avax\Components\Security\System\System\Capabilities\MassAssignment;

use InvalidArgumentException;

final readonly class MassAssignmentGuard
{
    /**
     * @param list<string> $fillable
     */
    public function onlyFillable(array $input, array $fillable): array
    {
        $allowed = array_flip(array: $fillable);
        $unknown = array_diff_key($input, $allowed);

        if ($unknown !== []) {
            throw new InvalidArgumentException(message: 'Mass assignment rejected keys: ' . implode(separator: ', ', array: array_keys(array: $unknown)));
        }

        return array_intersect_key($input, $allowed);
    }
}

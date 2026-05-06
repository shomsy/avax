<?php

declare(strict_types=1);

namespace Avax\Components\Security\System\Capabilities\MassAssignment;

use InvalidArgumentException;

final readonly class MassAssignmentGuard
{
    /**
     * @param  array<string, mixed>  $input
     * @param  list<string>  $fillable
     * @return array<string, mixed>
     */
    public function onlyFillable(array $input, array $fillable): array
    {
        $allowed = array_flip(array: $fillable);
        $unknown = array_diff_key($input, $allowed);

        if ($unknown !== []) {
            throw new InvalidArgumentException(message: 'Mass assignment rejected keys: '.implode(separator: ', ', array: array_keys(array: $unknown)));
        }

        return array_intersect_key($input, $allowed);
    }
}

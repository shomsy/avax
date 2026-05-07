<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Foundation\Text;

final class NormalizeScope
{
    public function __invoke(string $scope) : string
    {
        $parts = preg_split(pattern: '/\s+/', subject: trim(string: $scope));
        $parts = is_array(value: $parts) ? $parts : [];
        $parts = array_values(array: array_filter(array: $parts, callback: static fn (string $value) : bool => $value !== ''));
        $parts = array_values(array: array_unique(array: $parts));
        sort(array: $parts);

        return implode(separator: ' ', array: $parts);
    }
}

<?php

declare(strict_types=1);

namespace Avax\Auth\System\Foundation\Text;

final class NormalizeScope
{
    public function __invoke(string $scope) : string
    {
        $parts = preg_split('/\s+/', trim($scope));
        $parts = is_array($parts) ? $parts : [];
        $parts = array_values(array_filter($parts, static fn (string $value) : bool => $value !== ''));
        $parts = array_values(array_unique($parts));
        sort($parts);

        return implode(' ', $parts);
    }
}

<?php

declare(strict_types=1);

namespace Avax\Auth\System\Foundation\Text;

final class MaskSecret
{
    public function __invoke(string $value, int $visiblePrefix = 4) : string
    {
        $length = strlen($value);

        if ($length <= $visiblePrefix) {
            return str_repeat('*', $length);
        }

        return substr($value, 0, $visiblePrefix) . str_repeat('*', $length - $visiblePrefix);
    }
}

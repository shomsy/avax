<?php

declare(strict_types=1);

namespace Avax\Auth\System\Foundation\Text;

final class MaskSecret
{
    public function __invoke(string $value, int $visiblePrefix = 4) : string
    {
        $length = strlen(string: $value);

        if ($length <= $visiblePrefix) {
            return str_repeat(string: '*', times: $length);
        }

        return substr(string: $value, offset: 0, length: $visiblePrefix) . str_repeat(string: '*', times: $length - $visiblePrefix);
    }
}

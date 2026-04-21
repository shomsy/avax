<?php

declare(strict_types=1);

namespace Avax\Auth\System\Foundation\Text;

final class NormalizeUsername
{
    public function __invoke(string $value) : string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $value) ?? $value));
    }
}

<?php

declare(strict_types=1);

namespace Avax\Tooling\PreCommit;

final class Shell
{
    public static function quote(string $value): string
    {
        return "'".str_replace("'", "'\\''", $value)."'";
    }
}

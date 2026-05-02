<?php

declare(strict_types=1);

namespace Avax\Components\Application\DateTime\System\Flows\Convert;

use DateTimeInterface;

final class ConvertToHuman
{
    public static function execute(DateTimeInterface $dt): string
    {
        return $dt->format('Y-m-d H:i:s');
    }
}

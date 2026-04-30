<?php

declare(strict_types=1);

namespace Avax\Components\Application\DateTime\System\Flows\Convert;

use DateTimeInterface;

final class ConvertToTimestamp
{
    public static function execute(DateTimeInterface $dt) : int
    {
        return $dt->getTimestamp();
    }
}

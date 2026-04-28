<?php

declare(strict_types=1);

namespace Avax\DateTime\System\Capabilities\Timezone;

use DateTimeInterface;
use DateTimeZone;

interface Timezone
{
    public function getName() : string;

    public function toPhpTimezone() : DateTimeZone;

    public function getOffset(DateTimeInterface $dateTime) : int;

    public function isUtc() : bool;
}
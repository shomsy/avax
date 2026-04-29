<?php
declare(strict_types=1);
namespace Avax\Components\Application\DateTime\System\Flows\Convert;
final class ConvertToTimestamp { public static function execute(\DateTimeInterface $dt): int { return $dt->getTimestamp(); } }

<?php
declare(strict_types=1);
namespace Avax\Components\Application\DateTime\System\Flows\Convert;
final class ConvertToIso8601 { public static function execute(\DateTimeInterface $dt): string { return $dt->format(\DateTime::ATOM); } }

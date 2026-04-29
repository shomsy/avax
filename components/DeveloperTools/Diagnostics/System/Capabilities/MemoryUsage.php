<?php
namespace Avax\Components\DeveloperTools\Diagnostics\System\Capabilities;
final class MemoryUsage { public static function execute(): int { return memory_get_usage(true); } }

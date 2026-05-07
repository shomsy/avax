<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\DumpDebugger\System\Flows\FormatVariable;

final readonly class FormatVariable
{
    public function format(mixed $value, int $maxDepth = 3, int $currentDepth = 0) : string
    {
        if ($currentDepth >= $maxDepth) {
            return '...';
        }

        if (is_array($value)) {
            $items = [];
            foreach ($value as $k => $v) {
                $items[] = "  [{$k}] => " . $this->format($v, $maxDepth, $currentDepth + 1);
            }

            return "Array\n(\n" . implode("\n", $items) . "\n)";
        }

        return var_export($value, true);
    }
}

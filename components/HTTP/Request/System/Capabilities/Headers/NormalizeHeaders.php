<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Capabilities\Headers;

final class NormalizeHeaders
{
    public function normalize(array $raw) : array
    {
        $norm = [];
        foreach ($raw as $n => $v) {
            $n = str_replace('_', '-', $n);
            if (str_starts_with($n, 'HTTP-')) {
                $n = substr($n, 5);
            }

            // V5-22: Cast values to string to handle float/int $_SERVER values.
            $norm[$n] = (string) $v;
        }

        return $norm;
    }
}

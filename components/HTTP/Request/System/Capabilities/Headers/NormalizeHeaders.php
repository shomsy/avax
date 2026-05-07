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

            $norm[$n] = $v;
        }

        return $norm;
    }
}

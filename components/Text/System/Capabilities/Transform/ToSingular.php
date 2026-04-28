<?php

declare(strict_types=1);

namespace Avax\Components\Text\System\Capabilities\Transform;

/**
 * Capability to singularize English words.
 */
final class ToSingular
{
    public function execute(string $value) : string
    {
        if (preg_match('/ies$/i', $value)) {
            return substr($value, 0, -3) . 'y';
        }
        if (preg_match('/es$/i', $value)) {
            return substr($value, 0, -2);
        }
        if (preg_match('/s$/i', $value)) {
            return substr($value, 0, -1);
        }

        return $value;
    }
}

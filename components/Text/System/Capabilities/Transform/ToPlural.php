<?php

declare(strict_types=1);

namespace Avax\Components\Text\System\Capabilities\Transform;

/**
 * Capability to pluralize English words.
 * Recovered from legacy Inflector.
 */
final class ToPlural
{
    public function execute(string $value) : string
    {
        if (preg_match('/[sxz]$|sh$|ch$/i', $value)) {
            return $value . 'es';
        }
        if (preg_match('/([^aeiou])y$/i', $value)) {
            return substr($value, 0, -1) . 'ies';
        }

        return $value . 's';
    }
}

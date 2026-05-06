<?php

declare(strict_types=1);

namespace Avax\Components\Application\Text\System\Capabilities\Transform;

/**
 * Capability to pluralize English words.
 * Recovered from legacy Inflector.
 */
final class ToPlural
{
    public function execute(string $value, int $count = 2): string
    {
        if ($count === 1) {
            return $value;
        }

        $irregular = [
            'person' => 'people', 'man' => 'men', 'woman' => 'women',
            'child' => 'children', 'foot' => 'feet', 'tooth' => 'teeth',
            'goose' => 'geese', 'mouse' => 'mice', 'ox' => 'oxen',
        ];

        $lower = strtolower($value);
        if (isset($irregular[$lower])) {
            $replacement = $irregular[$lower];

            return $value === ucfirst($value) ? ucfirst($replacement) : $replacement;
        }

        if (preg_match('/[sxz]$|sh$|ch$/i', $value)) {
            return $value.'es';
        }

        if (preg_match('/([^aeiou])y$/i', $value)) {
            return substr($value, 0, -1).'ies';
        }

        return $value.'s';
    }
}

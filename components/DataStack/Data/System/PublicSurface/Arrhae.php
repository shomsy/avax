<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\PublicSurface;

/**
 * Arrhae — array-native engine public entry point.
 *
 * Arrhae provides fluent array/pipeline operations.
 * It is the independent engine that Collection and Json compose.
 */
final class Arrhae
{
    private function __construct()
    {
    }

    /**
     * @param  iterable<array-key, mixed>  $items
     */
    public static function make(iterable $items = []): \Avax\Components\DataStack\Data\System\Capabilities\Forms\ArrayForm\Arrhae
    {
        return new \Avax\Components\DataStack\Data\System\Capabilities\Forms\ArrayForm\Arrhae(items: is_array($items) ? $items : iterator_to_array($items));
    }
}

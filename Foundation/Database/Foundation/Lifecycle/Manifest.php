<?php

declare(strict_types=1);

namespace Avax\Database\Lifecycle;

use Avax\Migrations\Module;

/**
 * Central discovery point for database feature modules.
 */
final class Manifest
{
    /**
     * Retrieve the map of active feature modules.
     *
     * @return array<string, string>
     */
    public static function getModules() : array
    {
        $potentialModules = [
            \Avax\Database\Transaction\Module::class,
            \Avax\Database\QueryBuilder\Module::class,
            Module::class,
        ];

        $registry = [];
        foreach ($potentialModules as $class) {
            if (class_exists(class: $class) && method_exists(object_or_class: $class, method: 'declare')) {
                $declaration                    = $class::declare();
                $registry[$declaration['name']] = $declaration['class'];
            }
        }

        return $registry;
    }
}

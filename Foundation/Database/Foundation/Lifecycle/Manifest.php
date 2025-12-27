<?php

declare(strict_types=1);

namespace Avax\Database\Lifecycle;

use Avax\Migrations\Module;

/**
 * Static registry for database module discovery and configuration.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/Concepts/Architecture.md
 */
final class Manifest
{
    /**
     * Retrieve the list of active database feature modules.
     *
     * @return array<string, string> Map of nicknames to class names.
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
            // We check if the class is actually there and if it follows our rules.
            if (class_exists(class: $class) && method_exists(object_or_class: $class, method: 'declare')) {
                $declaration                    = $class::declare();
                $registry[$declaration['name']] = $declaration['class'];
            }
        }

        return $registry;
    }
}

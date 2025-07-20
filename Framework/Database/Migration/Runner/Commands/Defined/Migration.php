<?php

declare(strict_types=1);

namespace Gemini\Database\Migration\Runner\Commands\Defined;

/**
 * Class Migration
 *
 * This class defines a set of migration commands and their corresponding aliases.
 * It helps in standardizing the command names for various migration operations.
 */
class Migration
{
    /**
     * Get the list of migration commands and their aliases.
     *
     * This method returns an associative array mapping custom command names to
     * their actual migration command counterparts. The intent is to provide a
     * simpler and more standardized way to refer to common migration operations.
     *
     * @return array<string, string> Returns an associative array of command aliases.
     */
    public static function definedCommandAliases() : array
    {
        return [
            'migrate:up'       => 'migrate',
            'migrate:down'     => 'migrate:rollback',
            'migrate:reapply'  => 'migrate:refresh',
            'migrate:clean'    => 'migrate:fresh',
            'create:migration' => 'make:migration',
        ];
    }
}
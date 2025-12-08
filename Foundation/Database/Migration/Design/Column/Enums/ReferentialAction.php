<?php

/**
 * ReferentialAction Enum for Database Foreign Key Constraints
 *
 * This enum defines the possible actions that can be taken when a referenced
 * record is deleted or updated in a foreign key relationship.
 *
 * @package Avax\Database\Migration\Table\Column
 * @since   1.0.0
 * @immutable
 */
declare(strict_types=1);

namespace Avax\Database\Migration\Design\Column\Enums;

enum ReferentialAction: string
{
    /**
     * Automatically delete or update related records when the referenced record is deleted/updated
     *
     * @var string
     */
    case CASCADE = 'CASCADE';

    /**
     * Set the foreign key column value to NULL when the referenced record is deleted/updated
     *
     * @var string
     */
    case SET_NULL = 'SET NULL';

    /**
     * Prevent deletion/update of referenced record if it has related records
     *
     * @var string
     */
    case RESTRICT = 'RESTRICT';

    /**
     * Similar to RESTRICT, prevents changes that would violate referential integrity
     *
     * @var string
     */
    case NO_ACTION = 'NO ACTION';

    /**
     * Set the foreign key column to its default value when the referenced record is deleted/updated
     *
     * @var string
     */
    case SET_DEFAULT = 'SET DEFAULT';
}
<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Design\Table\Alter\DTO;

use Avax\Database\Migration\Design\Column\DSL\ColumnDefinition;
use Avax\Database\Migration\Design\Table\Alter\Definitions\Base\AlterColumnDefinition;
use Avax\Database\Migration\Design\Table\Alter\Enums\AlterType;

/**
 * Represents an immutable value object for table alteration operations.
 *
 * This DTO encapsulates the essential information needed to perform
 * structural modifications to database tables, ensuring type safety
 * and immutability in the domain model.
 *
 * @final    Prevents extension to maintain invariants
 * @readonly Ensures immutability of the value object
 */
final readonly class AlterOperation
{
    /**
     * Constructs a new AlterOperation instance using constructor promotion.
     *
     * Encapsulates the complete state required for a table alteration
     * operation through immutable properties, following DDD value object patterns.
     *
     * @param AlterType                                   $type                                                                                                       The
     *                                                                                                                                                                perform
     * @param string                                      $target                                                                                                     The
     *                                                                                                                                                                identifier
     * @param ColumnDefinition|AlterColumnDefinition|null $definition                                                                                                 The
     *                                                                                                                                                                specification
     */
    public function __construct(
        public AlterType                                   $type,
        public string                                      $target,
        public ColumnDefinition|AlterColumnDefinition|null $definition = null
    ) {}
}
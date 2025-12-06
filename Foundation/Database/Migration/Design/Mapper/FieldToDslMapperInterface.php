<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Design\Mapper;

use Avax\Database\Migration\Design\Table\Table;
use Avax\Database\Migration\Runner\DTO\FieldDTO;

/**
 * Defines the contract for mapping field data transfer objects to database schema DSL.
 *
 * This interface is part of the database migration domain and implements the Strategy pattern,
 * allowing for flexible field-to-DSL mapping strategies. It serves as a crucial component
 * in translating field definitions into concrete database schema specifications.
 *
 * Key responsibilities:
 * - Translates FieldDTO objects into table schema modifications
 * - Ensures consistent field mapping across different database platforms
 * - Maintains single responsibility principle for field transformation logic
 *
 * @package Avax\Database\Migration\Design\Mapper
 * @since   8.3.0
 */
interface FieldToDslMapperInterface
{
    /**
     * Applies the field mapping strategy to transform a FieldDTO into table schema modifications.
     *
     * This method implements the core mapping logic, taking a table instance and field DTO
     * as input and applying the necessary schema modifications through the table's DSL.
     *
     * @param Table    $table The target table to apply the field mapping to
     * @param FieldDTO $field The field data transfer object containing the field definition
     *
     * @return void
     *
     * @throws \InvalidArgumentException If the field definition is invalid
     * @throws \RuntimeException If the mapping operation fails
     */
    public function apply(Table $table, FieldDTO $field) : void;
}
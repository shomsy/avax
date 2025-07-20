<?php

declare(strict_types=1);

namespace Gemini\Database\Migration\Design\Table\Traits;

use Gemini\Database\Migration\Design\Mapper\FieldToDslMapperInterface;
use Gemini\Database\Migration\Runner\DTO\FieldDTO;
use RuntimeException;

/**
 * Provides field mapping capabilities for database schema definitions.
 *
 * This trait implements the Strategy pattern to enable dynamic field-to-DSL mapping
 * in database table definitions. It serves as a bridge between FieldDTO objects
 * and the table's DSL methods.
 *
 * Key Features:
 * - Implements a Strategy pattern for flexible field mapping
 * - Supports both single and batch field operations
 * - Provides fluent interface for method chaining
 * - Maintains loose coupling through dependency injection
 *
 * @template T of object
 * @author YourName <your@email.com>
 * @since  8.3.0
 */
trait FieldMappingTrait
{
    /**
     * Field-to-DSL mapper implementation.
     *
     * Responsible for transforming FieldDTO objects into table column definitions
     * using the fluent DSL. Implements the Strategy pattern to allow runtime
     * mapping behavior modification.
     *
     * @var FieldToDslMapperInterface|null
     */
    private FieldToDslMapperInterface|null $mapper = null;

    /**
     * Configures the field mapping strategy.
     *
     * Injects the mapper implementation that will be used for converting FieldDTO
     * objects into table column definitions via the fluent DSL.
     *
     * @param FieldToDslMapperInterface $mapper The field mapping strategy to use
     *
     * @return T The trait using instance for method chaining
     */
    public function useMapper(FieldToDslMapperInterface $mapper) : self
    {
        $this->mapper = $mapper;

        return $this;
    }

    /**
     * Applies multiple field definitions to the table schema.
     *
     * Batch processes an array of FieldDTO objects, applying each one to the table
     * schema using the configured mapper.
     *
     * @param array<int, FieldDTO> $fields Collection of field definitions to apply
     *
     * @return T The trait using instance for method chaining
     */
    public function applyMany(array $fields) : self
    {
        foreach ($fields as $field) {
            $this->apply(field: $field);
        }

        return $this;
    }

    /**
     * Applies a single field definition to the table schema.
     *
     * Delegates the field-to-column mapping to the injected mapper strategy,
     * enforcing the requirement for a configured mapper.
     *
     * @param FieldDTO $field The field definition to apply
     *
     * @return T The trait using instance for method chaining
     * @throws RuntimeException When no mapper has been configured
     */
    public function apply(FieldDTO $field) : self
    {
        if (! $this->mapper) {
            throw new RuntimeException(message: 'No FieldToDslMapperInterface injected into Table.');
        }

        $this->mapper->apply(table: $this, field: $field);

        return $this;
    }
}
<?php

declare(strict_types=1);

namespace Gemini\Database\Migration\Runner\DTO;

use Gemini\DataHandling\ObjectHandling\DTO\AbstractDTO;
use Gemini\DataHandling\Validation\Attributes\Rules\ArrayType;
use Gemini\DataHandling\Validation\Attributes\Rules\Required;

/**
 * Data Transfer Object (DTO) representing a database schema.
 *
 * Primary purpose:
 * - Facilitates the consistent and strongly typed representation of schema-related data throughout the system.
 * - Encapsulates and validates an array of fields, where each field is defined by an instance of `FieldDTO`.
 *
 * Leveraging DDD Practices:
 * - Serves as a Boundary Data Design for interaction between application layers.
 * - Ensures domain consistency by enforcing attribute-based validations (e.g., `#[Required]`, `#[ArrayType]`).
 *
 * @package Application\DTO
 */
class SchemaDTO extends AbstractDTO
{
    /**
     * A list of field definitions forming a database schema.
     *
     * - Represents the core building blocks of a database schema (e.g., columns, field attributes).
     * - Each field within the array is strongly typed as `FieldDTO`, ensuring schema integrity.
     *
     * Validation Requirements:
     * - **Required:** `fields` must be present and cannot be `null`.
     * - **ArrayType:** It must be an array of well-formed `FieldDTO` instances.
     *
     * @var \Gemini\Database\Migration\Runner\DTO\FieldDTO[] $fields
     *
     */
    #[Required]
    #[ArrayType]
    public array $fields;
}
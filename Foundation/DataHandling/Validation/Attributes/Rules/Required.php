<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Attribute to enforce that a property (or a nested path) must be provided and not null.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Required
{
    /**
     * Path within the object to validate (e.g., 'schema.fields')
     *
     * @var string|null
     */
    private string|null $path;

    /**
     * Custom validation error message.
     *
     * @var string|null
     */
    private string|null $message;

    /**
     * Constructor
     *
     * @param string|null $path    Optional deep path to validate (e.g., 'schema.fields')
     * @param string|null $message Optional custom error message
     */
    public function __construct(string|null $path = null, string|null $message = null)
    {
        $this->path    = $path;
        $this->message = $message;
    }

    /**
     * Validates a required value, supporting deep paths like 'schema.fields'.
     *
     * @param mixed  $value    The full object or field to validate.
     * @param string $property The property name being validated.
     *
     * @throws ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        $target = $value;

        if ($this->path !== null) {
            // Traverse nested properties (e.g., schema.fields)
            foreach (explode('.', $this->path) as $segment) {
                if (is_array($target) && array_key_exists($segment, $target)) {
                    $target = $target[$segment];
                } elseif (is_object($target) && isset($target->$segment)) {
                    $target = $target->$segment;
                } else {
                    $target = null;
                    break;
                }
            }
        }

        if ($target === null) {
            throw new ValidationException(
                message : $this->message ?? sprintf(
                'The "%s" field is required and cannot be null.',
                $this->path ?? $property
            ),
                metadata: ['property' => $this->path ?? $property]
            );
        }
    }
}

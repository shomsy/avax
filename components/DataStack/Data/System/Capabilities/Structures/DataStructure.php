<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures;

/**
 * DataStructure - Defines the shape and constraints of a data object.
 */
class DataStructure
{
    /** @var array<string, bool> */
    private array $fields = [];

    /**
     * @param iterable<string> $requiredFields
     */
    public function __construct(iterable $requiredFields = [])
    {
        foreach ($requiredFields as $requiredField) {
            $this->require(field: $requiredField);
        }
    }

    public function require(string $field) : void
    {
        if ($field !== '') {
            $this->fields[$field] = true;
        }
    }

    public function has(string $field) : bool
    {
        return isset($this->fields[$field]);
    }

    /**
     * @return list<string>
     */
    public function requiredFields() : array
    {
        return array_keys(array: $this->fields);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return list<string>
     */
    public function missingFields(array $data) : array
    {
        $missing = [];

        foreach (array_keys(array: $this->fields) as $field) {
            if (! array_key_exists(key: $field, array: $data)) {
                $missing[] = $field;
            }
        }

        return $missing;
    }
}

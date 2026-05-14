<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection;

final readonly class DataShape
{
    /**
     * @param class-string             $class
     * @param array<string, DataField> $fields
     */
    public function __construct(
        public string $class,
        private array $fields,
    ) {}

    /**
     * @return array<string, DataField>
     */
    public function fields() : array
    {
        return $this->fields;
    }

    /**
     * @return array<string, DataField>
     */
    public function constructorFields() : array
    {
        return array_filter(
            array   : $this->fields,
            callback: static fn (DataField $dataField) : bool => $dataField->isConstructorField,
        );
    }

    /**
     * @return array<string, DataField>
     */
    public function publicPropertyFields() : array
    {
        return array_filter(
            array   : $this->fields,
            callback: static fn (DataField $dataField) : bool => $dataField->isPublicProperty,
        );
    }

    public function field(string $name) : DataField|null
    {
        return $this->fields[$name] ?? null;
    }

    /**
     * @return list<string>
     */
    public function inputNames() : array
    {
        return array_values(
            array: array_map(
                       callback: static fn (DataField $dataField) : string => $dataField->inputName,
                       array   : $this->fields,
                   ),
        );
    }
}

<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\DefineSaga;

final readonly class DescribeSagaStep
{
    public function __construct(
        public string $name,
        public string $description,
        public array  $inputSchema,
        public array  $outputSchema,
        public bool   $compensatable
    ) {}

    public function describeResponsibility() : string
    {
        return 'describes a saga step including name, description, schemas, and compensatability.';
    }

    public static function create(
        string $name,
        string $description,
        bool   $compensatable = true
    ) : self
    {
        return new self(
            name         : $name,
            description  : $description,
            inputSchema  : [],
            outputSchema : [],
            compensatable: $compensatable
        );
    }

    public function toMetadata() : array
    {
        return [
            'name'          => $this->name,
            'description'   => $this->description,
            'input_schema'  => $this->inputSchema,
            'output_schema' => $this->outputSchema,
            'compensatable' => $this->compensatable,
        ];
    }
}
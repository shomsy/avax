<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Capabilities\Commands;

use Avax\Components\CLI\Console\System\Capabilities\Generators\EntityGenerator;
use Avax\Components\CLI\Console\System\PublicSurface\Command;
use Override;
use RuntimeException;

/**
 * Command to generate a new entity class.
 */
class MakeEntityCommand extends Command
{
    protected string $name        = 'make:entity';

    protected string $description = 'Create a new entity class';

    protected string $signature   = 'make:entity {name} [--table=] [--fields=]';

    protected array $arguments = ['name'];

    protected array $options = ['table', 'fields'];

    public function __construct(
        private readonly EntityGenerator $entityGenerator,
    ) {}

    #[Override]
    protected function handle() : int
    {
        $name = $this->argument(0);

        if (empty($name)) {
            $name = $this->ask('Enter entity name');

            if ($name === '' || $name === '0') {
                $this->error('Entity name is required.');

                return self::INVALID;
            }
        }

        $table       = $this->option('table');
        $fieldsInput = $this->option('fields');
        $fields      = [];

        if (is_string($fieldsInput) && $fieldsInput !== '') {
            $fields = $this->parseFields($fieldsInput);
        }

        try {
            $data = [];

            if ($table !== null) {
                $data['table'] = $table;
            }

            if ($fields !== []) {
                $data['fields'] = $fields;
            }

            $path = $this->entityGenerator->generate($name, $data);

            $this->info('Entity created successfully: ' . $path);

            return self::SUCCESS;
        } catch (RuntimeException $runtimeException) {
            $this->error('Failed to create entity: ' . $runtimeException->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Parse field string "name:type,name:type" into array.
     */
    private function parseFields(string $input) : array
    {
        $fields = [];

        foreach (explode(',', $input) as $pair) {
            $parts     = explode(':', trim($pair), 2);
            $fieldName = $parts[0] ?? '';
            $fieldType = $parts[1] ?? 'string';

            if ($fieldName !== '') {
                $fields[] = ['name' => $fieldName, 'type' => $fieldType];
            }
        }

        return $fields;
    }
}

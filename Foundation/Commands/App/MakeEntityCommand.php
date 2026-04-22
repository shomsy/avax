<?php

declare(strict_types=1);

namespace Avax\Commands\App;

use Avax\Commands\App\Contracts\EntityGeneratorInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class MakeEntityCommand
{
    private LoggerInterface $logger;
    private EntityGeneratorInterface $entityGenerator;

    public function __construct(
        EntityGeneratorInterface $entityGenerator,
        LoggerInterface          $logger
    )
    {
        $this->entityGenerator = $entityGenerator;
        $this->logger          = $logger;
    }

    public function execute(array $arguments) : void
    {
        $table       = $arguments['table'] ?? null;
        $fieldsInput = $arguments['fields'] ?? '';

        if (empty($table)) {
            $this->logger->error(message: 'Table name is required for entity generation.');
            echo "Error: Table name is required.\n";

            return;
        }

        try {
            $fields = $this->parseFields(fieldsInput: $fieldsInput);
            $this->entityGenerator->create(tableName: $table, fields: $fields);
            $this->logger->info(message: sprintf("Entity for table '%s' created successfully.", $table));
        } catch (Throwable $throwable) {
            $this->logger->error(message: 'Error creating entity: ' . $throwable->getMessage());
        }
    }

    private function parseFields(string $fieldsInput) : array
    {
        if ($fieldsInput === '' || $fieldsInput === '0') {
            return [];
        }

        $fields = [];
        foreach (explode(separator: ',', string: $fieldsInput) as $pair) {
            [$name, $type] = explode(separator: ':', string: $pair) + [1 => 'string'];
            $fields[] = ['name' => $name, 'type' => $type];
        }

        return $fields;
    }
}

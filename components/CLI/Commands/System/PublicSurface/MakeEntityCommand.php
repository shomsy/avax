<?php
declare(strict_types=1);

namespace Avax\Components\CLI\Commands\System\PublicSurface;

use Avax\Components\CLI\Commands\System\Capabilities\Generators\EntityGeneratorInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class MakeEntityCommand
{
    public function __construct(
        private EntityGeneratorInterface $generator,
        private LoggerInterface $logger
    ) {}

    public function execute(array $arguments): void
    {
        $table = $arguments['table'] ?? null;
        $fieldsInput = $arguments['fields'] ?? '';

        if (empty($table)) {
            $this->logger->error('Table name is required.');
            return;
        }

        try {
            $fields = $this->parseFields($fieldsInput);
            $this->generator->create($table, $fields);
            $this->logger->info("Entity for table '$table' created successfully.");
        } catch (Throwable $e) {
            $this->logger->error('Error creating entity: ' . $e->getMessage());
        }
    }

    private function parseFields(string $input): array
    {
        if ($input === '') return [];
        $fields = [];
        foreach (explode(',', $input) as $pair) {
            [$name, $type] = explode(':', $pair) + [1 => 'string'];
            $fields[] = ['name' => $name, 'type' => $type];
        }
        return $fields;
    }
}

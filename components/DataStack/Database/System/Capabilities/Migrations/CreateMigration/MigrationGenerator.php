<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations\CreateMigration;

use Avax\Components\Operations\Filesystem\System\PublicSurface\Filesystem;
use DateTime;

/**
 * Migration file generator using external stubs.
 *
 * -- intent: create new migration files by populating templates.
 */
final class MigrationGenerator
{
    /**
     * Generate a new migration file.
     *
     * @param  string  $name  Migration name (e.g., 'create_users_table')
     * @param  string  $path  Target directory
     * @param  string|null  $table  Associated table name
     * @param  bool  $create  Whether this is a creation migration
     * @return string Created file path
     */
    public function generate(string $name, string $path, ?string $table = null, bool $create = false): string
    {
        $timestamp = $this->getTimestamp();
        $className = $this->getClassName(name: $name);
        $filename = sprintf('%s_%s.php', $timestamp, $name);
        $filepath = rtrim(string: $path, characters: DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$filename;

        $stub = $this->getStubContent(table: $table, create: $create);
        $content = $this->populateStub(stub: $stub, className: $className, table: $table);

        Filesystem::write(path: $filepath, content: $content);

        return $filepath;
    }

    private function getTimestamp(): string
    {
        return new DateTime()->format(format: 'Y_m_d_His');
    }

    private function getClassName(string $name): string
    {
        return str_replace(search: '_', replace: ' ', subject: $name)
                |> ucwords(...)
                |> (static fn ($x): string|array => str_replace(search: ' ', replace: '', subject: $x));
    }

    private function getStubContent(?string $table, bool $create): string
    {
        $stubName = 'blank.stub';

        if ($table !== null) {
            $stubName = $create ? 'create.stub' : 'update.stub';
        }

        $stubPath = __DIR__.DIRECTORY_SEPARATOR.'Stubs'.DIRECTORY_SEPARATOR.$stubName;

        return Filesystem::read(path: $stubPath);
    }

    private function populateStub(string $stub, string $className, ?string $table): string
    {
        $replacements = [
            '{{className}}' => $className,
            '{{table}}' => $table ?? '',
        ];

        return str_replace(
            search : array_keys(array: $replacements),
            replace: array_values(array: $replacements),
            subject: $stub,
        );
    }
}

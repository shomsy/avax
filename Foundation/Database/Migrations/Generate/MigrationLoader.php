<?php

declare(strict_types=1);

namespace Avax\Migrations\Generate;

use Avax\Migrations\Design\BaseMigration;
use DirectoryIterator;

/**
 * Migration file loader and instantiator.
 *
 * -- intent: discover and load migration files from filesystem.
 */
final class MigrationLoader
{
    /**
     * Calculate the checksum for a migration file.
     */
    public function getChecksum(string $name, string $path) : string
    {
        $file = rtrim(string: $path, characters: DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name . '.php';

        if (! file_exists(filename: $file)) {
            return '';
        }

        return md5_file(filename: $file);
    }

    public function getPending(string $path, array $ran) : array
    {
        $all = $this->load(path: $path);

        $ranNames = array_column(array: $ran, column_key: 'migration');

        return array_filter(
            array   : $all,
            callback: static fn($name) => ! in_array(needle: $name, haystack: $ranNames, strict: true),
            mode    : ARRAY_FILTER_USE_KEY
        );
    }

    public function load(string $path) : array
    {
        if (! is_dir(filename: $path)) {
            return [];
        }

        $migrations = [];
        $files      = $this->getMigrationFiles(path: $path);

        foreach ($files as $file) {
            $migration = $this->loadMigrationFile(file: $file);

            if ($migration !== null) {
                $migrations[$this->getMigrationName(file: $file)] = $migration;
            }
        }

        ksort(array: $migrations);

        return $migrations;
    }

    private function getMigrationFiles(string $path) : array
    {
        $files = [];

        foreach (new DirectoryIterator(directory: $path) as $fileInfo) {
            if ($fileInfo->isDot() || ! $fileInfo->isFile()) {
                continue;
            }

            if ($fileInfo->getExtension() === 'php') {
                $files[] = $fileInfo->getPathname();
            }
        }

        sort(array: $files);

        return $files;
    }

    private function loadMigrationFile(string $file) : BaseMigration|null
    {
        $migration = require $file;

        if ($migration instanceof BaseMigration) {
            return $migration;
        }

        return null;
    }

    public function getMigrationName(string $file) : string
    {
        return str_replace(search: '.php', replace: '', subject: basename(path: $file));
    }
}

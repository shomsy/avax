<?php

declare(strict_types=1);

namespace Avax\Components\Security\Privacy\System\Capabilities\DataExporter;

use RuntimeException;

final class DataExporter
{
    /**
     * @param array<string, mixed> $data
     */
    public function export(array $data, string $format = 'json') : string
    {
        return match ($format) {
            'csv'   => $this->exportCsv(data: $data),
            default => $this->exportJson(data: $data),
        };
    }

    /**
     * @param array<string, mixed> $data
     */
    public function exportCsv(array $data) : string
    {
        if (empty($data)) {
            return '';
        }

        $output = fopen(filename: 'php://temp', mode: 'r+');

        if ($output === false) {
            throw new RuntimeException(message: 'Failed to open temp stream');
        }

        $headers = array_keys(array: $data[array_key_first(array: $data)]);
        fputcsv(stream: $output, fields: $headers);

        foreach ($data as $row) {
            fputcsv(stream: $output, fields: array_values(array: $row));
        }

        rewind(stream: $output);
        $csv = stream_get_contents(stream: $output);
        fclose(stream: $output);

        return $csv ?: '';
    }

    /**
     * @param array<string, mixed> $data
     */
    public function exportJson(array $data) : string
    {
        return json_encode(value: $data, flags: JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    }
}

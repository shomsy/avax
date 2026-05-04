<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ContentNegotiation\System\Capabilities\Formats;

final class CsvFormat implements ContentFormatterInterface
{
    public function format(mixed $data): string
    {
        if (! is_array($data)) {
            return (string) $data;
        }

        $handle = fopen('php://temp', 'r+');

        if (isset($data[0]) && is_array($data[0])) {
            fputcsv($handle, array_keys($data[0]), escape: '\\');

            foreach ($data as $row) {
                fputcsv($handle, $row, escape: '\\');
            }
        } else {
            fputcsv($handle, array_keys($data), escape: '\\');
            fputcsv($handle, array_values($data), escape: '\\');
        }

        rewind($handle);
        $result = stream_get_contents($handle);
        fclose($handle);

        return $result;
    }

    public function mimeType(): string
    {
        return 'text/csv';
    }
}

<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ContentNegotiation\System\PublicSurface;

final class CsvFormatter implements ContentFormatter
{
    public function format(mixed $data): string
    {
        if (! is_array($data)) {
            return (string) $data;
        }

        $output = fopen('php://temp', 'r+');

        if (isset($data[0]) && is_array($data[0])) {
            fputcsv($output, array_keys($data[0]), escape: '\\');

            foreach ($data as $row) {
                fputcsv($output, $row, escape: '\\');
            }
        } else {
            fputcsv($output, array_keys($data), escape: '\\');
            fputcsv($output, array_values($data), escape: '\\');
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return $content;
    }

    public function mimeType(): string
    {
        return 'text/csv';
    }
}

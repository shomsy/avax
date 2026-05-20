<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ContentNegotiation\System\PublicSurface;

use Avax\Components\HTTP\ContentNegotiation\System\Capabilities\Formats\NeutralizeFormulaCell;

final class CsvFormatter implements ContentFormatter
{
    public function format(mixed $data) : string
    {
        if (! is_array($data)) {
            return (string) $data;
        }

        $output = fopen('php://temp', 'r+');

        if (isset($data[0]) && is_array($data[0])) {
            fputcsv($output, array_keys($data[0]), escape: '\\');

            foreach ($data as $row) {
                fputcsv($output, $this->neutralizeRow($row), escape: '\\');
            }
        } else {
            fputcsv($output, array_keys($data), escape: '\\');
            fputcsv($output, $this->neutralizeRow(array_values($data)), escape: '\\');
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return $content;
    }

    public function mimeType() : string
    {
        return 'text/csv';
    }

    /**
     * Neutralize formula injection characters in every cell of a row.
     *
     * @param  list<mixed>  $row
     * @return list<string>
     */
    private function neutralizeRow(array $row) : array
    {
        $escaped = [];

        foreach ($row as $cell) {
            $escaped[] = NeutralizeFormulaCell::escape($cell);
        }

        return $escaped;
    }
}

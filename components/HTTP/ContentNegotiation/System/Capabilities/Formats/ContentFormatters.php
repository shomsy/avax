<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ContentNegotiation\System\Capabilities\Formats;

use SimpleXMLElement;

interface ContentFormatterInterface
{
    public function format(mixed $data): string;

    public function mimeType(): string;
}

final class JsonFormat implements ContentFormatterInterface
{
    public function format(mixed $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_APOS);
    }

    public function mimeType(): string
    {
        return 'application/json';
    }
}

final class XmlFormat implements ContentFormatterInterface
{
    public function format(mixed $data): string
    {
        if (is_array($data)) {
            $xml = new SimpleXMLElement('<root/>');
            $this->toXmlRecursive($data, $xml);

            return $xml->asXML();
        }

        return (string) $data;
    }

    private function toXmlRecursive(array $data, SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $child = $xml->addChild(is_numeric($key) ? 'item' : $key);
                $this->toXmlRecursive($value, $child);
            } else {
                $xml->addChild(is_numeric($key) ? 'item' : $key, (string) $value);
            }
        }
    }

    public function mimeType(): string
    {
        return 'application/xml';
    }
}

final class CsvFormat implements ContentFormatterInterface
{
    public function format(mixed $data): string
    {
        if (! is_array($data)) {
            return (string) $data;
        }

        $handle = fopen('php://temp', 'r+');

        if (isset($data[0]) && is_array($data[0])) {
            fputcsv($handle, array_keys($data[0]));

            foreach ($data as $row) {
                fputcsv($handle, $row);
            }
        } else {
            fputcsv($handle, array_keys($data));
            fputcsv($handle, array_values($data));
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

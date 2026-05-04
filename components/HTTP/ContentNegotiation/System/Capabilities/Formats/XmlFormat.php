<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ContentNegotiation\System\Capabilities\Formats;

use SimpleXMLElement;

final class XmlFormat implements ContentFormatterInterface
{
    public function format(mixed $data): string
    {
        if (is_array($data)) {
            $xml = new SimpleXMLElement('<root/>');
            $this->toXmlRecursive($data, $xml);

            return $xml->asXML();
        }

        return (string)$data;
    }

    private function toXmlRecursive(array $data, SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $child = $xml->addChild(is_numeric($key) ? 'item' : $key);
                $this->toXmlRecursive($value, $child);
            } else {
                $xml->addChild(is_numeric($key) ? 'item' : $key, (string)$value);
            }
        }
    }

    public function mimeType(): string
    {
        return 'application/xml';
    }
}

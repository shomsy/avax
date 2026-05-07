<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ContentNegotiation\System\System\PublicSurface;

use SimpleXMLElement;

final class XmlFormatter implements ContentFormatter
{
    public function format(mixed $data) : string
    {
        if (is_array($data)) {
            return $this->arrayToXml($data);
        }

        return (string) $data;
    }

    private function arrayToXml(array $data) : string
    {
        $xml = new SimpleXMLElement('<root/>');

        $this->arrayToXmlRecursive($data, $xml);

        return $xml->asXML();
    }

    private function arrayToXmlRecursive(array $data, SimpleXMLElement $xml) : void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $child = $xml->addChild(is_numeric($key) ? 'item' : $key);
                $this->arrayToXmlRecursive($value, $child);
            } else {
                $xml->addChild(is_numeric($key) ? 'item' : $key, (string) $value);
            }
        }
    }

    public function mimeType() : string
    {
        return 'application/xml';
    }
}

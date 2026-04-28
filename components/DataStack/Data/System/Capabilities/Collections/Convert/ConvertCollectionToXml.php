<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Convert;

use Exception;
use LogicException;
use SimpleXMLElement;

/**
 * Converts collection to XML.
 */
final readonly class ConvertCollectionToXml
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke(string $rootElement = 'root') : string
    {
        return $this->toXml(rootElement: $rootElement);
    }

    public function toXml(string $rootElement = 'root') : string
    {
        try {
            $xml = new SimpleXMLElement(data: "<{$rootElement}/>");
            $this->arrayToXml(data: $this->items, xml: $xml);

            return $xml->asXML() ?: '';
        } catch (Exception $exception) {
            throw new LogicException(
                message : 'Failed to convert collection to XML: ' . $exception->getMessage(),
                code    : $exception->getCode(),
                previous: $exception
            );
        }
    }

    private function arrayToXml(array $data, SimpleXMLElement $xml) : void
    {
        foreach ($data as $key => $value) {
            $tagName = is_numeric(value: $key) ? 'item' : $key;

            if (is_array(value: $value)) {
                $child = $xml->addChild(qualifiedName: $tagName);
                $this->arrayToXml(data: $value, xml: $child);
            } else {
                $xml->addChild(qualifiedName: $tagName, value: htmlspecialchars(string: (string) $value));
            }
        }
    }

    public function getItems() : array
    {
        return $this->items;
    }
}

<?php

declare(strict_types=1);

namespace Avax\DataHandling\Traits\Conversion;

use Exception;
use InvalidArgumentException;
use LogicException;
use SimpleXMLElement;

/**
 * Provides conversion methods: toJson, toArray, toXml.
 */
trait ConversionTrait
{
    abstract protected function getItems(): array;

    public function toJson(int $flags = 0): string
    {
        $json = json_encode($this->getItems(), $flags);

        if ($json === false) {
            throw new InvalidArgumentException(
                'Failed to encode collection to JSON: ' . json_last_error_msg()
            );
        }

        return $json;
    }

    public function toArray(): array
    {
        return array_map(
            static fn(mixed $item): mixed => $this->normalizeItem($item),
            $this->getItems()
        );
    }

    public function toXml(string $rootElement = 'root'): string
    {
        try {
            $xml = new SimpleXMLElement("<{$rootElement}/>");
            $this->arrayToXml($this->getItems(), $xml);

            return $xml->asXML() ?: '';
        } catch (Exception $exception) {
            throw new LogicException(
                'Failed to convert collection to XML: ' . $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
    }

    public function only(array $keys): static
    {
        if ($keys === []) {
            throw new InvalidArgumentException('Keys array cannot be empty.');
        }

        $filtered = array_filter(
            $this->getItems(),
            static fn(mixed $_, mixed $key): bool => in_array($key, $keys, true),
            ARRAY_FILTER_USE_BOTH
        );

        return $this->withItems($filtered);
    }

    public function except(array $keys): static
    {
        if ($keys === []) {
            throw new InvalidArgumentException('Keys array cannot be empty.');
        }

        $filtered = array_filter(
            $this->getItems(),
            static fn(mixed $_, mixed $key): bool => ! in_array($key, $keys, true),
            ARRAY_FILTER_USE_BOTH
        );

        return $this->withItems($filtered);
    }

    private function normalizeItem(mixed $item): mixed
    {
        if (is_object($item) && method_exists($item, 'toArray')) {
            return $item->toArray();
        }

        return $item;
    }

    private function arrayToXml(array $data, SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            $tagName = is_numeric($key) ? 'item' : $key;

            if (is_array($value)) {
                $child = $xml->addChild($tagName);
                $this->arrayToXml($value, $child);
            } else {
                $xml->addChild($tagName, htmlspecialchars((string) $value));
            }
        }
    }

    abstract protected function withItems(array $items): static;
}
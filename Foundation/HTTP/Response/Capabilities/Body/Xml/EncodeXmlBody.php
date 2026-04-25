<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Body\Xml;

use InvalidArgumentException;
use SimpleXMLElement;

final class EncodeXmlBody
{
    public function __invoke(string|array $xml, string $rootElement = 'response') : string
    {
        if (is_string(value: $xml)) {
            return $xml;
        }

        $rootElement = new ValidateXmlElementName()(elementName: $rootElement);
        $document    = new SimpleXMLElement(data: "<{$rootElement}/>");
        $this->append(node: $document, payload: $xml);

        $encoded = $document->asXML();
        if ($encoded === false) {
            throw new InvalidArgumentException(message: 'Unable to encode the XML response body.');
        }

        return $encoded;
    }

    private function append(SimpleXMLElement $node, array $payload) : void
    {
        foreach ($payload as $key => $value) {
            $elementName = is_string(value: $key)
                ? new ValidateXmlElementName()(elementName: $key)
                : 'item';

            if (is_array(value: $value)) {
                $child = $node->addChild(qualifiedName: $elementName);
                $this->append(node: $child, payload: $value);
                continue;
            }

            if (is_object(value: $value) && ! method_exists(object_or_class: $value, method: '__toString')) {
                throw new InvalidArgumentException(message: 'XML response arrays cannot contain non-stringable objects.');
            }

            $node->addChild(qualifiedName: $elementName, value: htmlspecialchars((string) $value, ENT_XML1 | ENT_COMPAT, 'UTF-8'));
        }
    }
}

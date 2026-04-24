<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Body\Xml;

use InvalidArgumentException;

final class ValidateXmlElementName
{
    public function __invoke(string $elementName) : string
    {
        if (! preg_match(pattern: '/^[A-Za-z_][A-Za-z0-9._-]*$/', subject: $elementName)) {
            throw new InvalidArgumentException(message: "Invalid XML element name [{$elementName}].");
        }

        return $elementName;
    }
}

<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Flows\BuildResponse;

use Avax\HTTP\Response\Capabilities\Body\Xml\EncodeXmlBody;
use Psr\Http\Message\ResponseInterface;

final class BuildXmlResponse
{
    public function __invoke(string|array $xml, int $status = 200, array $headers = []) : ResponseInterface
    {
        return (new BuildResponse())(
            status : $status,
            headers: ['Content-Type' => 'application/xml; charset=UTF-8', ...$headers],
            body   : (new EncodeXmlBody())($xml),
        );
    }
}

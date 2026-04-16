<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\Configuration\CreateRequestFromIncomingHttp;

/**
 * Public Entry Point: Static factory for creating ServerRequest instances.
 */
final readonly class PublicEntryPointRequest
{
    /**
     * Captures the current HTTP environment and returns a new ServerRequest.
     */
    public static function fromIncomingHttp() : ServerRequest
    {
        return CreateRequestFromIncomingHttp::capture();
    }
}

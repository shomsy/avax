<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\Configuration\PrepareRequest;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Mapping\MapRequestedInputsToDto;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Sanitization\InputSanitizer;
use ReflectionException;

/**
 * AssembleIncomingRequest - Public flow owner for request creation.
 */
final readonly class AssembleIncomingRequest
{
    public function __construct(
        private PrepareRequest          $preparer,
        private InputSanitizer          $sanitizer,
        private MapRequestedInputsToDto $mapper,
    ) {}

    /**
     * @throws ReflectionException
     */
    public function fromGlobals(
        array|null $server = null,
        array|null $query = null,
        array|null $cookie = null,
        array|null $files = null,
        string|null $rawBody = null,
    ) : ServerRequest
    {
        $init = $this->preparer->fromGlobals(
            server: $server,
            query : $query,
            cookie: $cookie,
            files : $files,
            rawBody: $rawBody,
        );

        return $this->create(init: $init);
    }

    public function fromSlices(
        array|null        $queryParams = null,
        array|object|null $parsedBody = null,
        string            $method = 'GET',
    ) : ServerRequest
    {
        $init = $this->preparer->fromSlices(
            queryParams: $queryParams,
            parsedBody : $parsedBody,
            method     : $method,
        );

        return $this->create(init: $init);
    }

    public function empty() : ServerRequest
    {
        return $this->create(init: $this->preparer->defaults());
    }

    private function create(RequestInit $init) : ServerRequest
    {
        return new ServerRequest(
            setup: new ServerInit(
                     state    : $init,
                     preparer : $this->preparer,
                     sanitizer: $this->sanitizer,
                     mapper   : $this->mapper,
                 )
        );
    }
}

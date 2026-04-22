<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\Configuration\PrepareRequest;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Mapping\MapRequestedInputsToDto;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Sanitization\InputSanitizer;

/**
 * ServerInit - Dependency context for ServerRequest.
 */
final readonly class ServerInit
{
    public function __construct(
        public RequestInit             $state,
        public PrepareRequest          $preparer,
        public InputSanitizer          $sanitizer,
        public MapRequestedInputsToDto $mapper,
    ) {}

    public function withState(RequestInit $state) : self
    {
        return new self(
            state    : $state,
            preparer : $this->preparer,
            sanitizer: $this->sanitizer,
            mapper   : $this->mapper,
        );
    }
}

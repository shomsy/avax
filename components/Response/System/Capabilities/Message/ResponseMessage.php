<?php

declare(strict_types=1);

namespace Avax\Components\Response\System\Capabilities\Message;

use Avax\HTTP\Response\Capabilities\Message\ResponseMessage as RealResponseMessage;

/**
 * ResponseMessage — delegates to the real PSR-7 ResponseInterface implementation.
 *
 * The real implementation has: ValidateStatusCode, ResolveReasonPhrase,
 * NormalizeProtocolVersion, ResponseHeaders, ResponseBody — all wired properly.
 */
class ResponseMessage extends RealResponseMessage {}

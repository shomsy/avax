<?php

declare(strict_types=1);

namespace Avax\Components\Request\System\PublicSurface;

use components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest as RealServerRequest;

/**
 * ServerRequest — Canonical public surface.
 *
 * Delegates to the real PSR-7 ServerRequest implementation at
 * HTTP/Request/ServerRequest/IncomingRequest/ServerRequest.php.
 *
 * The real implementation has: property hooks, immutable state via RequestInit,
 * RequestHeaders, RequestBody, ParsedBody, RequestCookies, UploadedFiles,
 * RequestAttributes, RequestSession, RequestedInputs (typed + sanitized + DTO mapping).
 *
 * This file exists to provide the canonical Screaming Architecture namespace.
 * All power comes from the real HTTP/Request engine.
 */
class ServerRequest extends RealServerRequest {}

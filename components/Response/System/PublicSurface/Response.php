<?php

declare(strict_types=1);

namespace Avax\Components\Response\System\PublicSurface;

use Avax\HTTP\Response\Response as RealResponse;

/**
 * Response — Canonical public surface.
 *
 * Delegates to the real Response facade at HTTP/Response/Response.php.
 *
 * The real implementation has: empty, text, html, json, xml, problem (RFC 9457),
 * redirect, download, stream, noContent, notModified, emit — all backed by
 * the full Capabilities ecosystem (Body, Caching, Cookies, Downloads, Headers,
 * Message, Redirects, Streams) and Flows (BuildResponse, EmitResponse).
 *
 * This file exists to provide the canonical Screaming Architecture namespace.
 * All power comes from the real HTTP/Response engine.
 */
class Response extends RealResponse {}

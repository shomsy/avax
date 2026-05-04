<?php

declare(strict_types=1);

namespace Avax\Components\Security\System\PublicSurface;

use Avax\Components\HTTP\Security\System\Capabilities\Csrf\CsrfToken;
use Avax\Components\HTTP\Security\System\Capabilities\Csrf\CsrfVerifier;
use Avax\Components\HTTP\Security\System\Capabilities\Headers\SecurityHeaders;
use Avax\Components\HTTP\Security\System\Capabilities\SignedUrls\SignedUrlGenerator;
use Avax\Components\HTTP\Security\System\Capabilities\SignedUrls\SignedUrlVerifier;
use Avax\Components\Security\System\Capabilities\Audit\SecurityAuditLog;
use Avax\Components\Security\System\Capabilities\Escape\OutputEscaper;
use Avax\Components\Security\System\Capabilities\MassAssignment\MassAssignmentGuard;

final class ResponseFormatter
{
    /** @var array<string, string> */
    private array $headers = [];

    public function withHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;

        return $clone;
    }

    public function headers(): array
    {
        return $this->headers;
    }
}

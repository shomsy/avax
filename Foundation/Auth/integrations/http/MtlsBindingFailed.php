<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

use RuntimeException;

final class MtlsBindingFailed extends RuntimeException
{
    public static function missingCertificate() : self
    {
        return new self('mTLS client certificate is required.');
    }

    public static function mismatch() : self
    {
        return new self('mTLS sender constraint does not match the token binding.');
    }
}

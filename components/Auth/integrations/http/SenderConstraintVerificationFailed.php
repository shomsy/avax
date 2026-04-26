<?php

declare(strict_types=1);

namespace components\Auth\Integrations\Http;

use RuntimeException;

final class SenderConstraintVerificationFailed extends RuntimeException
{
    public static function mismatch() : self
    {
        return new self(message: 'OAuth sender constraint does not match the bound token.');
    }
}

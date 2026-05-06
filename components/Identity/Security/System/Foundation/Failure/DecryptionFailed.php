<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\Foundation\Failure;

/**
 * Exception thrown when decryption operation fails.
 *
 * This includes:
 * - Tampered payloads (authentication tag mismatch)
 * - Wrong encryption key
 * - Corrupted or invalid payload data
 */
final class DecryptionFailed extends SecurityFailure
{
}

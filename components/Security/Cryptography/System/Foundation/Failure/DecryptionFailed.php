<?php

declare(strict_types=1);

namespace Avax\Components\Security\Cryptography\System\Foundation\Failure;

/**
 * Exception thrown when decryption operation fails.
 *
 * This includes:
 * - Tampered payloads (authentication tag mismatch)
 * - Wrong encryption key
 * - Corrupted or invalid payload data
 */
final class DecryptionFailed extends CryptographyFailure {}

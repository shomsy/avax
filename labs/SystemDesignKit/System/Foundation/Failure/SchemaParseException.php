<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Foundation\Failure;

use RuntimeException;

/**
 * Thrown when a schema validation file cannot be parsed or loaded.
 *
 * @experimental V3 labs
 */
final class SchemaParseException extends RuntimeException {}

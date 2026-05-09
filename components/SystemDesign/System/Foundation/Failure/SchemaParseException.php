<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Foundation\Failure;

use RuntimeException;

/**
 * Thrown when a schema validation file cannot be parsed or loaded.
 *
 * @experimental V3 labs
 */
final class SchemaParseException extends RuntimeException {}

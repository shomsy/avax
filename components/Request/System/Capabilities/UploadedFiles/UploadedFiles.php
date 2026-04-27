<?php

declare(strict_types=1);

namespace Avax\Components\Request\System\Capabilities\UploadedFiles;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\UploadedFiles as RealUploadedFiles;

/**
 * UploadedFiles — delegates to the real implementation.
 */
class UploadedFiles extends RealUploadedFiles {}

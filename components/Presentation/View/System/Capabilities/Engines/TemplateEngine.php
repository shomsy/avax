<?php

declare(strict_types=1);

namespace Avax\Components\Presentation\View\System\Capabilities\Engines;

use Avax\Components\Presentation\View\TemplateEngine as RealTemplateEngine;

/**
 * Advanced Template Engine based on BladeOne.
 *
 * Delegates to the real TemplateEngine implementation which includes
 * robust base URL handling and comprehensive custom directives.
 */
class TemplateEngine extends RealTemplateEngine {}

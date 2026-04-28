<?php

declare(strict_types=1);

namespace Avax\Components\Presentation\View\System\Capabilities\Engines;

use Avax\View\BladeTemplateEngine as RealBladeTemplateEngine;

/**
 * Ultimate Blade Template Engine.
 *
 * Delegates to the real BladeTemplateEngine implementation which includes
 * advanced asset path management, dynamic base URLs, and deep integration
 * with Avax's Markdown, Auth, and Routing capabilities.
 */
class BladeTemplateEngine extends RealBladeTemplateEngine {}

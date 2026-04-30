<?php

declare(strict_types=1);

namespace Avax\Components\Presentation\View\System\Capabilities\Engines;

use Jenssegers\Blade\Blade;

class BladeTemplateEngine extends Blade implements TemplateEngineInterface
{
    public function __construct(string $viewsPath, string $cachePath)
    {
        parent::__construct($viewsPath, $cachePath);
        $this->configureCustomDirectives();
    }

    private function configureCustomDirectives() : void
    {
        $this->compiler()->directive('csrf', static fn () => "<?php echo '<input type=\"hidden\" name=\"_token\" value=\"' . csrf_token() . '\">'; ?>");
        $this->compiler()->directive('method', static fn ($expression) => "<?php echo '<input type=\"hidden\" name=\"_method\" value=\"' . $expression . '\">'; ?>");
    }

    public function render(string $view, array $data = []) : string
    {
        return parent::render($view, $data);
    }
}

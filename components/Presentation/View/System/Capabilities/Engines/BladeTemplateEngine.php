<?php

declare(strict_types=1);

namespace Avax\Components\Presentation\View\System\Capabilities\Engines;

use Jenssegers\Blade\Blade;
use Override;

class BladeTemplateEngine extends Blade implements TemplateEngineInterface
{
    public function __construct(string $viewsPath, string $cachePath)
    {
        parent::__construct($viewsPath, $cachePath);
        $this->configureCustomDirectives();
    }

    private function configureCustomDirectives(): void
    {
        $this->compiler()->directive('csrf', static fn (): string => "<?php echo '<input type=\"hidden\" name=\"_token\" value=\"' . csrf_token() . '\">'; ?>");
        $this->compiler()->directive('method', static fn (string $expression) : string => sprintf("<?php echo '<input type=\"hidden\" name=\"_method\" value=\"' . %s . '\">'; ?>", $expression));
        $this->compiler()->directive('auth', static fn (): string => '<?php if (function_exists("auth") && auth()->check()): ?>');
        $this->compiler()->directive('endauth', static fn (): string => '<?php endif; ?>');
        $this->compiler()->directive('can', static fn (string $expression) : string => sprintf("<?php if (function_exists('policy') && policy()->allows%s): ?>", $expression));
        $this->compiler()->directive('endcan', static fn (): string => '<?php endif; ?>');
        $this->compiler()->directive('push', static fn (string $expression) : string => sprintf('<?php ob_start(); $__avax_push_name = %s; ?>', $expression));
        $this->compiler()->directive('endpush', static fn (): string => "<?php \$GLOBALS['__avax_view_stacks'][\$__avax_push_name][] = ob_get_clean(); ?>");
        $this->compiler()->directive('stack', static fn (string $expression) : string => sprintf("<?php echo implode('', \$GLOBALS['__avax_view_stacks'][%s] ?? []); ?>", $expression));
        $this->compiler()->directive('component', static fn (string $expression) : string => sprintf('<?php $__avax_component = %s; ob_start(); ?>', $expression));
        $this->compiler()->directive('slot', static fn (string $expression) : string => sprintf('<?php $__avax_slot = %s; ob_start(); ?>', $expression));
        $this->compiler()->directive('endslot', static fn (): string => "<?php \$GLOBALS['__avax_view_slots'][\$__avax_slot] = ob_get_clean(); ?>");
        $this->compiler()->directive('endcomponent', static fn (): string => "<?php echo view(\$__avax_component, ['slot' => ob_get_clean(), 'slots' => \$GLOBALS['__avax_view_slots'] ?? []])->getBody(); ?>");
    }

    #[Override]
    public function render(string $view, array $data = [], array $mergeData = []) : string
    {
        return parent::render($view, $data);
    }

    public function clearCompiledViews(): void
    {
        foreach (glob(pattern: rtrim(string: $this->getCachePath(), characters: '/') . '/*.php') ?: [] as $compiledView) {
            unlink(filename: $compiledView);
        }
    }
}

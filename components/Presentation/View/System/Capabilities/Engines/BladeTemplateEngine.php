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

    private function configureCustomDirectives(): void
    {
        $this->compiler()->directive('csrf', static fn (): string => "<?php echo '<input type=\"hidden\" name=\"_token\" value=\"' . csrf_token() . '\">'; ?>");
        $this->compiler()->directive('method', static fn ($expression): string => "<?php echo '<input type=\"hidden\" name=\"_method\" value=\"' . $expression . '\">'; ?>");
        $this->compiler()->directive('auth', static fn (): string => '<?php if (function_exists("auth") && auth()->check()): ?>');
        $this->compiler()->directive('endauth', static fn (): string => '<?php endif; ?>');
        $this->compiler()->directive('can', static fn ($expression): string => "<?php if (function_exists('policy') && policy()->allows{$expression}): ?>");
        $this->compiler()->directive('endcan', static fn (): string => '<?php endif; ?>');
        $this->compiler()->directive('push', static fn ($expression): string => "<?php ob_start(); \$__avax_push_name = {$expression}; ?>");
        $this->compiler()->directive('endpush', static fn (): string => "<?php \$GLOBALS['__avax_view_stacks'][\$__avax_push_name][] = ob_get_clean(); ?>");
        $this->compiler()->directive('stack', static fn ($expression): string => "<?php echo implode('', \$GLOBALS['__avax_view_stacks'][{$expression}] ?? []); ?>");
        $this->compiler()->directive('component', static fn ($expression): string => "<?php \$__avax_component = {$expression}; ob_start(); ?>");
        $this->compiler()->directive('slot', static fn ($expression): string => "<?php \$__avax_slot = {$expression}; ob_start(); ?>");
        $this->compiler()->directive('endslot', static fn (): string => "<?php \$GLOBALS['__avax_view_slots'][\$__avax_slot] = ob_get_clean(); ?>");
        $this->compiler()->directive('endcomponent', static fn (): string => "<?php echo view(\$__avax_component, ['slot' => ob_get_clean(), 'slots' => \$GLOBALS['__avax_view_slots'] ?? []])->getBody(); ?>");
    }

    public function render(string $view, array $data = []): string
    {
        return parent::render($view, $data);
    }

    public function clearCompiledViews(): void
    {
        foreach (glob(pattern: rtrim(string: $this->getCachePath(), characters: '/').'/*.php') ?: [] as $compiledView) {
            unlink(filename: $compiledView);
        }
    }
}

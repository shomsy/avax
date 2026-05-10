<?php

declare(strict_types=1);

namespace Avax\Components\Presentation\View\System\Capabilities\Engines;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem as ApplicationFilesystem;
use Avax\Components\Operations\Filesystem\System\PublicSurface\Filesystem;
use eftec\bladeone\BladeOne;
use Override;

class BladeTemplateEngine extends BladeOne implements TemplateEngineInterface
{
    public function __construct(string $viewsPath, string $cachePath)
    {
        parent::__construct($viewsPath, $cachePath);
        $this->configureCustomDirectives();
    }

    private function configureCustomDirectives(): void
    {
        $this->directive('csrf', static fn (): string => "<?php echo '<input type=\"hidden\" name=\"_token\" value=\"' . csrf_token() . '\">'; ?>");
        $this->directive('method', static fn (string $expression): string => sprintf("<?php echo '<input type=\"hidden\" name=\"_method\" value=\"' . %s . '\">'; ?>", $expression));
        $this->directive('auth', static fn (): string => '<?php if (function_exists("auth") && auth()->check()): ?>');
        $this->directive('endauth', static fn (): string => '<?php endif; ?>');
        $this->directive('can', static fn (string $expression): string => sprintf("<?php if (function_exists('policy') && policy()->allows%s): ?>", $expression));
        $this->directive('endcan', static fn (): string => '<?php endif; ?>');
        $this->directive('push', static fn (string $expression): string => sprintf('<?php ob_start(); $__avax_push_name = %s; ?>', $expression));
        $this->directive('endpush', static fn (): string => "<?php \$GLOBALS['__avax_view_stacks'][\$__avax_push_name][] = ob_get_clean(); ?>");
        $this->directive('stack', static fn (string $expression): string => sprintf("<?php echo implode('', \$GLOBALS['__avax_view_stacks'][%s] ?? []); ?>", $expression));
        $this->directive('component', static fn (string $expression): string => sprintf('<?php $__avax_component = %s; ob_start(); ?>', $expression));
        $this->directive('slot', static fn (string $expression): string => sprintf('<?php $__avax_slot = %s; ob_start(); ?>', $expression));
        $this->directive('endslot', static fn (): string => "<?php \$GLOBALS['__avax_view_slots'][\$__avax_slot] = ob_get_clean(); ?>");
        $this->directive('endcomponent', static fn (): string => "<?php echo view(\$__avax_component, ['slot' => ob_get_clean(), 'slots' => \$GLOBALS['__avax_view_slots'] ?? []])->getBody(); ?>");
    }

    #[Override]
    public function render(string $view, array $data = [], array $mergeData = []): string
    {
        return $this->run($view, $data);
    }

    public function clearCompiledViews(): void
    {
        $filesystem = new ApplicationFilesystem();

        foreach ($filesystem->listFilesByPattern(pattern: rtrim(string: $this->compiledPath, characters: '/') . '/*.php') as $compiledView) {
            Filesystem::delete(path: $compiledView);
        }
    }
}

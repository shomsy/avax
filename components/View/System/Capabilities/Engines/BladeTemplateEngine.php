<?php

declare(strict_types=1);

namespace Avax\Components\View\System\Capabilities\Engines;

use Jenssegers\Blade\Blade;
use Throwable;

/**
 * Ultimate Blade Template Engine recovered from avax-backup.txt.
 * Includes advanced asset path management, dynamic base URLs,
 * and deep integration with Avax's Markdown and Auth capabilities.
 */
final class BladeTemplateEngine extends Blade
{
    public function __construct(string $viewsPath, string $cachePath)
    {
        parent::__construct($viewsPath, $cachePath);
        $this->configureCustomDirectives();
    }

    private function configureCustomDirectives() : void
    {
        // Advanced Asset Directive with Dynamic Base URL
        $this->compiler()->directive('asset', function ($expression) {
            return "<?php echo (str_starts_with(ltrim({$expression}, \"'\\\"\"), 'public') ? '' : '/assets/') . ltrim({$expression}, \"'\\\"/\"); ?>";
        });

        // Datetime formatting
        $this->compiler()->directive('datetime', fn ($expression) => "<?php echo with({$expression})->format('Y-m-d H:i:s'); ?>");

        // CSRF with session token
        $this->compiler()->directive('csrf', fn () => '<?php echo "<input type=\"hidden\" name=\"_token\" value=\"" . (csrf_token() ?? "") . "\">"; ?>');

        // Markdown Support (Requires Parsedown or similar in vendor)
        $this->compiler()->directive('markdown', function ($expression) {
            return "<?php echo (new \Parsedown())->text({$expression}); ?>";
        });

        // Auth & Identity Directives
        $this->compiler()->directive('auth', fn () => '<?php if (auth()->check()): ?>');
        $this->compiler()->directive('endauth', fn () => '<?php endif; ?>');
        $this->compiler()->directive('guest', fn () => '<?php if (!auth()->check()): ?>');
        $this->compiler()->directive('endguest', fn () => '<?php endif; ?>');

        // Debugging
        $this->compiler()->directive('dd', fn ($expression) => "<?php die(var_dump({$expression})); ?>");

        // Form Methods
        $this->compiler()->directive('method', function ($expression) {
            return "<?php echo '<input type=\"hidden\" name=\"_method\" value=\"' . strtoupper({$expression}) . '\">'; ?>";
        });
    }

    public function renderView(string $view, array $data = []) : string
    {
        try {
            return $this->make($view, $data)->render();
        } catch (Throwable $e) {
            return "<div>View Error: {$e->getMessage()}</div>";
        }
    }
}

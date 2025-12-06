<?php
/** @noinspection GlobalVariableUsageInspection */

declare(strict_types=1);

namespace Avax\View;

use Jenssegers\Blade\Blade;
use Throwable;

class BladeTemplateEngine extends Blade
{
    public string $baseAssetPath;

    public function __construct(string $viewsPath, string $cachePath)
    {
        parent::__construct(viewPaths: $viewsPath, cachePath: $cachePath);
        $this->initializeBaseAssetPath();
        $this->configureCustomDirectives();
    }

    private function initializeBaseAssetPath() : void
    {
        // Define the base asset path dynamically
        $this->baseAssetPath = $this->getBaseUrl() . '/assets';
    }

    private function getBaseUrl() : string
    {
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $url    = parse_url((string) env('APP_URL', 'http://localhost'));
        $scheme = $url['scheme'] ?? 'http';

        return sprintf('%s://%s', $scheme, $host);
    }

    private function configureCustomDirectives() : void
    {
        // Asset directive
        $this->compiler()->directive(name: 'asset', handler: fn($expression) : string => sprintf(
            "<?php echo preg_match('/^public/', %s) ? '%s/' . ltrim(%s, '\"\\'/') : '%s/' . ltrim(%s, '\"\\'/'); ?>",
            $expression,
            $this->getBaseUrl(),
            $expression,
            $this->getBaseUrl(),
            $expression
        ));

        // Datetime directive
        $this->compiler()->directive(name: 'datetime', handler: static fn($expression) : string => sprintf(
            "<?php echo with(%s)->format('Y-m-d H:i:s'); ?>",
            $expression
        ));

        // CSRF directive
        $this->compiler()->directive(name: 'csrf', handler: static fn(
        ) : string => "<?php echo '<input type=\"hidden\" name=\"_token\" value=\"' . csrf_token() . '\">'; ?>");

        // Route directive
        $this->compiler()->directive(name: 'route', handler: static fn($expression) : string => sprintf(
            '<?php echo route(%s); ?>',
            $expression
        ));

        // Checked directive
        $this->compiler()->directive(name: 'checked', handler: static fn($expression) : string => sprintf(
            "<?php echo %s ? 'checked' : ''; ?>",
            $expression
        ));

        // Selected directive
        $this->compiler()->directive(name: 'selected', handler: static fn($expression) : string => sprintf(
            "<?php echo %s ? 'selected' : ''; ?>",
            $expression
        ));

        // Dump directive
        $this->compiler()->directive(
            name   : 'dump',
            handler: static fn($expression) : string => sprintf(
                '<?php var_dump(%s); ?>',
                $expression
            )
        );

        // Die and dump directive
        $this->compiler()->directive(name: 'dd', handler: static fn($expression) : string => sprintf(
            '<?php die(var_dump(%s)); ?>',
            $expression
        ));

        // Markdown directive
        $this->compiler()->directive(name: 'markdown', handler: static fn($expression) : string => sprintf(
            '<?php echo (new Parsedown())->text(%s); ?>',
            $expression
        ));

        // AuthFacadeService directives
        $this->compiler()->directive(name: 'auth', handler: static fn() : string => "<?php if (auth()->check()): ?>");

        $this->compiler()->directive(name: 'endauth', handler: static fn() : string => "<?php endif; ?>");

        $this->compiler()->directive(name: 'guest', handler: static fn() : string => "<?php if (!auth()->check()): ?>");

        $this->compiler()->directive(name: 'endguest', handler: static fn() : string => "<?php endif; ?>");

        // Environment directive
        $this->compiler()->directive(name: 'ifenv', handler: static fn($expression) : string => sprintf(
            "<?php if (config('cashback.env') === %s): ?>",
            $expression
        ));

        $this->compiler()->directive(name: 'endifenv', handler: static fn() : string => "<?php endif; ?>");

        // IncludeWhen directive
        $this->compiler()->directive(name: 'includeWhen', handler: static fn($expression) : string => sprintf(
            "<?php if (%s) { include '%s'; } ?>",
            $expression[0],
            $expression[1]
        ));

        // HTTP method directive
        $this->compiler()->directive(name: 'method', handler: static fn($expression) : string => sprintf(
            "<?php echo '<input type=\"hidden\" name=\"_method\" value=\"' . %s . '\">'; ?>",
            $expression
        ));
    }

    public function toHtml(string $view, array $data = []) : string
    {
        try {
            return $this->render($view, $data);
        } catch (Throwable $throwable) {
            logger('View rendering to html failed.', ['view' => $view, 'exception' => $throwable]);

            return "<div>View rendering error: " . $throwable->getMessage() . "</div>";
        }
    }
}

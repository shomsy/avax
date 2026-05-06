<?php

/** @noinspection GlobalVariableUsageInspection */

declare(strict_types=1);

namespace Avax\Components\Presentation\View;

use Avax\Components\HTTP\Context\System\PublicSurface\HttpContextInterface;
use eftec\bladeone\BladeOne;
use Throwable;

class BladeTemplateEngine extends BladeOne
{
    public string $baseAssetPath;

    public function __construct(string $viewsPath, string $cachePath)
    {
        parent::__construct(templatePath: $viewsPath, compiledPath: $cachePath);
        $this->initializeBaseAssetPath();
        $this->configureCustomDirectives();
    }

    private function initializeBaseAssetPath(): void
    {
        // Define the base asset path dynamically
        $this->baseAssetPath = $this->getBaseUrl().'/assets';
    }

    public function getBaseUrl() : string
    {
        $context = function_exists(function: 'http_context') ? http_context() : null;
        if ($context instanceof HttpContextInterface) {
            return $context->baseUrl();
        }

        $url = parse_url(url: (string) config(key: 'app.url', default: 'http://localhost'));
        $scheme = $url['scheme'] ?? 'http';
        $host = $url['host'] ?? 'localhost';

        return sprintf('%s://%s', $scheme, $host);
    }

    private function configureCustomDirectives(): void
    {
        // Asset directive
        $this->directive(name: 'asset', handler: fn (string $expression): string => sprintf(
            "<?php echo preg_match('/^public/', %s) ? '%s/' . ltrim(%s, '\"\\'/') : '%s/' . ltrim(%s, '\"\\'/'); ?>",
            $expression,
            $this->getBaseUrl(),
            $expression,
            $this->getBaseUrl(),
            $expression,
        ));

        // Datetime directive
        $this->directive(name: 'datetime', handler: static fn (string $expression): string => sprintf(
            "<?php echo with(%s)->format('Y-m-d H:i:s'); ?>",
            $expression,
        ));

        // CSRF directive
        $this->directive(name: 'csrf', handler: static fn (): string => "<?php echo '<input type=\"hidden\" name=\"_token\" value=\"' . csrf_token() . '\">'; ?>");

        // Route directive
        $this->directive(name: 'route', handler: static fn (string $expression): string => sprintf(
            '<?php echo route(%s); ?>',
            $expression,
        ));

        // Checked directive
        $this->directive(name: 'checked', handler: static fn (string $expression): string => sprintf(
            "<?php echo %s ? 'checked' : ''; ?>",
            $expression,
        ));

        // Selected directive
        $this->directive(name: 'selected', handler: static fn (string $expression): string => sprintf(
            "<?php echo %s ? 'selected' : ''; ?>",
            $expression,
        ));

        // Dump directive
        $this->directive(
            name   : 'dump',
            handler: static fn (string $expression): string => sprintf(
                '<?php var_dump(%s); ?>',
                $expression,
            ),
        );

        // Die and dump directive
        $this->directive(name: 'dd', handler: static fn (string $expression): string => sprintf(
            '<?php die(var_dump(%s)); ?>',
            $expression,
        ));

        // Markdown directive
        $this->directive(name: 'markdown', handler: static fn (string $expression): string => sprintf(
            '<?php echo (new Parsedown())->text(%s); ?>',
            $expression,
        ));

        // AuthFacadeService directives
        $this->directive(name: 'auth', handler: static fn (): string => '<?php if (auth()->check()): ?>');

        $this->directive(name: 'endauth', handler: static fn (): string => '<?php endif; ?>');

        $this->directive(name: 'guest', handler: static fn (): string => '<?php if (!auth()->check()): ?>');

        $this->directive(name: 'endguest', handler: static fn (): string => '<?php endif; ?>');

        // Environment directive
        $this->directive(name: 'ifenv', handler: static fn (string $expression): string => sprintf(
            "<?php if (config('cashback.env') === %s): ?>",
            $expression,
        ));

        $this->directive(name: 'endifenv', handler: static fn (): string => '<?php endif; ?>');

        // IncludeWhen directive
        $this->directive(name: 'includeWhen', handler: static fn ($expression): string => sprintf(
            "<?php if (%s) { include '%s'; } ?>",
            $expression[0],
            $expression[1],
        ));

        // HTTP method directive
        $this->directive(name: 'method', handler: static fn (string $expression): string => sprintf(
            "<?php echo '<input type=\"hidden\" name=\"_method\" value=\"' . %s . '\">'; ?>",
            $expression,
        ));
    }

    public function toHtml(string $view, array $data = []): string
    {
        try {
            return $this->run(view: $view, variables: $data);
        } catch (Throwable $throwable) {
            logger(message: 'View rendering to html failed.', context: ['view' => $view, 'exception' => $throwable]);

            return '<div>View rendering error: '.$throwable->getMessage().'</div>';
        }
    }
}

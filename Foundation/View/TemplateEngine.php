<?php

declare(strict_types=1);

/**
 * Extends BladeOne templating engine to incorporate custom directives and asset path management.
 *
 * This class not only serves as a thin wrapper around BladeOne but also integrates the base URL,
 * asset path configurations, and other custom directives, making it adaptable for different environments.
 */

namespace Avax\View;

use eftec\bladeone\BladeOne;

class TemplateEngine extends BladeOne
{
    /**
     * The base URL for assets used in templating.
     */
    private string $baseAssetPath;

    /**
     * TemplateEngine constructor.
     *
     * @param  string  $templatePath  The path to template files.
     * @param  string  $compiledPath  The path where compiled templates are stored.
     * @param  int  $mode  BladeOne mode (e.g., MODE_AUTO).
     *
     * @throws \Avax\Container\Core\Exceptions\FoundationContainerException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(
        string $templatePath,
        string $compiledPath,
        int $mode = BladeOne::MODE_AUTO,
    ) {
        // Initialize the parent BladeOne class with provided paths and mode
        parent::__construct(
            templatePath: $templatePath,
            compiledPath: $compiledPath,
            mode        : $mode,
        );

        $this->initializeBaseAssetPath();
        $this->configureAssetDirective();
        $this->configureDateTimeDirective();
        $this->configureEnvironmentDirective();
        $this->configureMarkdownDirective();
        $this->configureRouteDirective();
        $this->configureCsrfDirective();
        $this->configureDumpDirective();
        $this->configureAuthDirectives();
        $this->configureIncludeWhenDirective();
        $this->configureMethodDirective();
        $this->configureCheckedDirective();
        $this->configureSelectedDirective();
    }

    /**
     * @throws \Avax\Container\Core\Exceptions\FoundationContainerException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function initializeBaseAssetPath(): void
    {
        // Retrieve the base URL and append the asset directory from configuration
        $this->baseAssetPath = $this->getBaseUrl().config(key: 'views.assets');
    }

    /**
     * Determine the base URL considering HTTP/HTTPS and host.
     *
     * @return string The base URL.
     *
     * This method dynamically constructs the base URL making it flexible for different environments and protocols.
     */
    public function getBaseUrl(): string
    {
        $context = function_exists('http_context') ? http_context() : null;
        if ($context !== null) {
            return $context->baseUrl();
        }

        return 'http://localhost';
    }

    /**
     * Configure a Blade directive for asset paths.
     *
     * This directive allows usage of @asset in Blade templates to reference assets relative to the base asset path.
     */
    private function configureAssetDirective(): void
    {
        $this->directive(
            name   : 'asset',
            handler: fn ($expression): string => sprintf(
                "<?php echo '%s/' . ltrim(%s, '\"\\'/'); ?>",
                $this->baseAssetPath,
                $expression
            ),
        );
    }

    /**
     * Configures a custom Blade directive for formatting DateTime objects.
     *
     * Allows templates to use a simple `@datetime` directive to format dates,
     * enhancing readability and consistency across templates.
     */
    private function configureDateTimeDirective(): void
    {
        $this->directive(
            name   : 'datetime',
            handler: static fn ($expression): string => sprintf(
                "<?php echo (new DateTime(%s))->format('Y-m-d H:i:s'); ?>",
                $expression
            ),
        );
    }

    /**
     * Configures custom Blade directives for environment-based conditional statements.
     *
     * Adds 'ifenv' and 'endifenv' directives for conditional content rendering based on
     * application's environment settings. Supports clean conditional checks in templates.
     */
    private function configureEnvironmentDirective(): void
    {
        $this->directive(
            name   : 'ifenv',
            handler: static fn ($expression): string => sprintf(
                "<?php if (config('cashback.env') === %s): ?>",
                $expression
            ),
        );
        $this->directive(
            name   : 'endifenv',
            handler: static fn (): string => '<?php endif; ?>',
        );
    }

    /**
     * Configures a custom Blade directive for handling Markdown within templates.
     *
     * This method defines the 'markdown' directive, which leverages the Parsedown library
     * to convert Markdown syntax into HTML. Allows easy embedding of Markdown content.
     */
    private function configureMarkdownDirective(): void
    {
        $this->directive(
            name   : 'markdown',
            handler: static fn ($expression): string => sprintf(
                '<?php echo (new Parsedown())->text(%s); ?>',
                $expression
            ),
        );
    }

    /**
     * Configures a directive for generating URL routes dynamically in templates.
     *
     * Allows usage of `@route` for clean URL generation within Blade templates,
     * enabling route-based link creation without hardcoding URLs.
     */
    private function configureRouteDirective(): void
    {
        $this->directive(
            name   : 'route',
            handler: static fn ($expression): string => sprintf('<?php echo route(%s); ?>', $expression),
        );
    }

    /**
     * Configures a Blade directive for generating CSRF tokens within forms.
     *
     * Enables easy addition of CSRF tokens via `@csrf` in form templates for security.
     */
    private function configureCsrfDirective(): void
    {
        $this->directive(
            name   : 'csrf',
            handler: static fn (): string => "<?php echo '<input type=\"hidden\" name=\"_token\" value=\"' . csrf_token() . '\">'; ?>",
        );
    }

    /**
     * Configures directives `@dump` and `@dd` for debugging.
     *
     * `@dump` outputs variable data; `@dd` outputs data and terminates script execution.
     * Useful for debugging variables within templates.
     */
    private function configureDumpDirective(): void
    {
        $this->directive(
            name   : 'dump',
            handler: static fn ($expression): string => sprintf('<?php var_dump(%s); ?>', $expression),
        );
        $this->directive(
            name   : 'dd',
            handler: static fn ($expression): string => sprintf('<?php die(var_dump(%s)); ?>', $expression),
        );
    }

    private function configureAuthDirectives(): void
    {
        $this->directive(
            name   : 'auth',
            handler: static fn (): string => '<?php if (auth()->check()): ?>',
        );
        $this->directive(
            name   : 'endauth',
            handler: static fn (): string => '<?php endif; ?>',
        );
        $this->directive(
            name   : 'guest',
            handler: static fn (): string => '<?php if (!auth()->check()): ?>',
        );
        $this->directive(
            name   : 'endguest',
            handler: static fn (): string => '<?php endif; ?>',
        );
    }

    /**
     * Configures the `@includeWhen` directive to conditionally include templates.
     *
     * `@includeWhen(condition, view)` includes a view template based on a condition.
     */
    private function configureIncludeWhenDirective(): void
    {
        $this->directive(
            name   : 'includeWhen',
            handler: static fn ($expression): string => sprintf(
                "<?php if (%s) { include '%s' ; } ?>",
                $expression[0],
                $expression[1]
            ),
        );
    }

    /**
     * Configures the `@method` directive for hidden HTTP method inputs in forms.
     *
     * Enables form support for HTTP methods like PUT and DELETE.
     */
    private function configureMethodDirective(): void
    {
        $this->directive(
            name   : 'method',
            handler: static fn (
                $expression,
            ): string => sprintf(
                "<?php echo '<input type=\"hidden\" name=\"_method\" value=\"' . %s . '\">'; ?>",
                $expression
            ),
        );
    }

    /**
     * Configures the `@checked` directive to add `checked` attribute based on condition.
     *
     * Adds `checked` attribute to checkboxes or radio buttons conditionally.
     */
    private function configureCheckedDirective(): void
    {
        $this->directive(
            name   : 'checked',
            handler: static fn ($expression): string => sprintf("<?php echo %s ? 'checked' : ''; ?>", $expression),
        );
    }

    /**
     * Configures the `@selected` directive to add `selected` attribute based on condition.
     *
     * Adds `selected` attribute to dropdown options conditionally.
     */
    private function configureSelectedDirective(): void
    {
        $this->directive(
            name   : 'selected',
            handler: static fn ($expression): string => sprintf("<?php echo %s ? 'selected' : ''; ?>", $expression),
        );
    }
}

<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleRuntimeFailure;

use Throwable;

/**
 * Renders error responses appropriate for the current environment.
 *
 * Development: Detailed error pages with stack traces and context.
 * Production: Generic error pages that don't leak sensitive information.
 */
final readonly class RenderRuntimeFailure
{
    private const string DEVELOPMENT_TEMPLATE
        = <<<'HTML'
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Runtime Error - AvaX Framework</title>
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 40px; background: #f5f5f5; color: #333; }
                    .container { max-width: 900px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
                    .header { background: #dc3545; color: white; padding: 20px 30px; }
                    .header h1 { margin: 0; font-size: 24px; }
                    .header p { margin: 5px 0 0; opacity: 0.9; font-size: 14px; }
                    .content { padding: 30px; }
                    .error-box { background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; padding: 15px; margin-bottom: 20px; }
                    .error-box h3 { margin: 0 0 10px; color: #721c24; }
                    .error-box p { margin: 5px 0; font-family: monospace; font-size: 13px; }
                    .trace { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 15px; }
                    .trace h3 { margin: 0 0 15px; }
                    .trace ol { margin: 0; padding-left: 20px; }
                    .trace li { margin-bottom: 8px; font-family: monospace; font-size: 12px; line-height: 1.5; }
                    .trace li strong { color: #495057; }
                    .context { margin-top: 20px; }
                    .context h3 { margin: 0 0 10px; }
                    .context pre { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 15px; overflow-x: auto; font-size: 12px; }
                    .meta { margin-top: 20px; padding-top: 20px; border-top: 1px solid #dee2e6; font-size: 12px; color: #6c757d; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>Runtime Error</h1>
                        <p>AvaX Framework - Development Mode</p>
                    </div>
                    <div class="content">
                        <div class="error-box">
                            <h3>Error Details</h3>
                            <p><strong>Exception:</strong> %s</p>
                            <p><strong>Message:</strong> %s</p>
                            <p><strong>File:</strong> %s</p>
                            <p><strong>Line:</strong> %d</p>
                        </div>
                      
                        <div class="trace">
                            <h3>Stack Trace</h3>
                            <ol>%s</ol>
                        </div>
                      
                        <div class="context">
                            <h3>Request Context</h3>
                            <pre>%s</pre>
                        </div>
                      
                        <div class="meta">
                            <p>Correlation ID: %s | Trace ID: %s | Timestamp: %s</p>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            HTML;

    private const string PRODUCTION_TEMPLATE
        = <<<'HTML'
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Error</title>
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f5f5f5; color: #333; }
                    .container { text-align: center; padding: 40px; }
                    .container h1 { font-size: 48px; margin: 0 0 10px; color: #dc3545; }
                    .container h2 { font-size: 24px; margin: 0 0 20px; color: #495057; font-weight: normal; }
                    .container p { color: #6c757d; margin: 0; }
                    .container a { display: inline-block; margin-top: 20px; color: #007bff; text-decoration: none; }
                    .container a:hover { text-decoration: underline; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>500</h1>
                    <h2>Internal Server Error</h2>
                    <p>Something went wrong. Please try again later.</p>
                    <p style="margin-top: 10px; font-size: 12px;">Error ID: %s</p>
                </div>
            </body>
            </html>
            HTML;

    private const string CLI_PRODUCTION_TEMPLATE = "[ERROR] Internal Server Error. Error ID: %s\n";

    private const string CLI_DEVELOPMENT_TEMPLATE
        = <<<'TXT'
            ========================================
              RUNTIME ERROR
            ========================================
            Exception: %s
            Message: %s
            File: %s:%d
                      
            Stack Trace:
            %s
            ========================================
            TXT;

    public function __construct(
        private string $environment = 'production',
        private string|null $correlationId = null,
        private string|null $traceId = null,
    ) {
    }

    /**
     * Render the error response and output it.
     */
    public function render(Throwable $throwable): void
    {
        $isCli = PHP_SAPI === 'cli';

        if ($isCli) {
            $this->renderCli($throwable);

            return;
        }

        $this->renderHttp($throwable);
    }

    /**
     * Render error output for CLI environment.
     */
    private function renderCli(Throwable $throwable): void
    {
        if ($this->isDevelopment()) {
            $traceOutput = '';
            $index = 1;

            foreach ($throwable->getTrace() as $frame) {
                $file = $frame['file'] ?? '[internal]';
                $line = $frame['line'] ?? 0;
                $class = $frame['class'] ?? '';
                $type = $frame['type'] ?? '';
                $function = $frame['function'];

                $traceOutput .= sprintf(
                    "  #%d %s%s%s(%s:%d)\n",
                    $index,
                    $class,
                    $type,
                    $function,
                    $file,
                    $line,
                );
                $index++;
            }

            fprintf(
                STDERR,
                self::CLI_DEVELOPMENT_TEMPLATE,
                $throwable::class,
                $throwable->getMessage(),
                $throwable->getFile(),
                $throwable->getLine(),
                $traceOutput,
            );
        } else {
            $errorId = $this->correlationId ?? 'unknown';
            fprintf(STDERR, self::CLI_PRODUCTION_TEMPLATE, $errorId);
        }
    }

    /**
     * Check if the current environment is development.
     */
    private function isDevelopment(): bool
    {
        return in_array($this->environment, ['development', 'dev', 'local', 'testing'], true);
    }

    /**
     * Render error output for HTTP environment.
     */
    private function renderHttp(Throwable $throwable): void
    {
        if (! headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }

        if ($this->isDevelopment()) {
            echo $this->renderDevelopmentPage($throwable);
        } else {
            echo $this->renderProductionPage();
        }
    }

    /**
     * Render detailed development error page.
     */
    private function renderDevelopmentPage(Throwable $throwable): string
    {
        $traceHtml = '';
        $index = 1;

        foreach ($throwable->getTrace() as $frame) {
            $file = htmlspecialchars($frame['file'] ?? '[internal]', ENT_QUOTES, 'UTF-8');
            $line = (int) ($frame['line'] ?? 0);
            $class = htmlspecialchars($frame['class'] ?? '', ENT_QUOTES, 'UTF-8');
            $type = htmlspecialchars($frame['type'] ?? '', ENT_QUOTES, 'UTF-8');
            $function = htmlspecialchars($frame['function'], ENT_QUOTES, 'UTF-8');

            $traceHtml .= sprintf(
                '<li><strong>#%d</strong> %s%s%s at <strong>%s:%d</strong></li>',
                $index,
                $class,
                $type,
                $function,
                $file,
                $line,
            );
            $index++;
        }

        $contextData = [
            'request' => $_SERVER,
            'session' => $_SESSION ?? [],
        ];

        $encodedContext = json_encode($contextData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($encodedContext === false) {
            $encodedContext = '{}';
        }

        return sprintf(
            self::DEVELOPMENT_TEMPLATE,
            htmlspecialchars($throwable::class, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($throwable->getMessage(), ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($throwable->getFile(), ENT_QUOTES, 'UTF-8'),
            $throwable->getLine(),
            $traceHtml,
            htmlspecialchars($encodedContext, ENT_QUOTES, 'UTF-8'),
            $this->correlationId ?? 'N/A',
            $this->traceId ?? 'N/A',
            date('Y-m-d H:i:s'),
        );
    }

    /**
     * Render generic production error page.
     */
    private function renderProductionPage(): string
    {
        $errorId = $this->correlationId ?? bin2hex(random_bytes(8));

        return sprintf(self::PRODUCTION_TEMPLATE, $errorId);
    }
}

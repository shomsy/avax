<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleRuntimeFailure;

use Throwable;

/**
 * Main flow class that orchestrates runtime error handling.
 *
 * Pipeline: ConvertPhpErrorToThrowable -> ReportRuntimeFailure -> RenderRuntimeFailure
 */
final readonly class HandleRuntimeFailure
{
    public function __construct(
        private ConvertPhpErrorToThrowable $convertPhpErrorToThrowable = new ConvertPhpErrorToThrowable(),
        private ReportRuntimeFailure $reportRuntimeFailure = new ReportRuntimeFailure(),
        private RenderRuntimeFailure $renderRuntimeFailure = new RenderRuntimeFailure(),
    ) {
    }

    /**
     * Handle a PHP error by converting it to a throwable, reporting it, and rendering a response.
     */
    public function handle(int $severity, string $message, string $file, int $line): never
    {
        $throwable = $this->convertPhpErrorToThrowable->convert($severity, $message, $file, $line);

        $this->reportRuntimeFailure->report($throwable);

        $this->renderRuntimeFailure->render($throwable);

        exit(1);
    }

    /**
     * Handle an uncaught exception by reporting and rendering it.
     */
    public function handleException(Throwable $throwable): never
    {
        $this->reportRuntimeFailure->report($throwable);

        $this->renderRuntimeFailure->render($throwable);

        exit(1);
    }

    /**
     * Handle a fatal error captured during shutdown.
     *
     * @param array{type: int, message: string, file: string, line: int} $error
     */
    public function handleFatalError(array $error): void
    {
        $throwable = $this->convertPhpErrorToThrowable->convertFromErrorArray($error);

        $this->reportRuntimeFailure->report($throwable);

        $this->renderRuntimeFailure->render($throwable);
    }
}

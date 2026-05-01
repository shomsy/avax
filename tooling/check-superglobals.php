<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$root         = dirname(path: __DIR__);
$patterns     = ['$_SERVER', '$_GET', '$_POST', '$_COOKIE', '$_FILES', '$_SESSION', '$_REQUEST'];
$scanRoots = [
    'components',
    'framework',
];
$allowedFiles = [
    'components/Application/Config/System/Capabilities/EnvironmentAwareness/Detection/EnvironmentDetector.php',
    'components/CLI/Console/System/PublicSurface/Console.php',
    'components/HTTP/Context/System/Capabilities/Globals/PhpGlobalsProvider.php',
    'components/HTTP/Context/System/PublicSurface/shortcuts.php',
    'components/HTTP/Request/System/Flows/CreateRequestFromGlobals/CreateRequestFromGlobals.php',
    'components/HTTP/Request/System/Flows/CreateRequestFromGlobals/ReadQueryParameters.php',
    'components/HTTP/Request/System/Flows/CreateRequestFromGlobals/ReadServerParameters.php',
    'components/HTTP/Request/System/Flows/CreateRequestFromGlobals/ReadUploadedFiles.php',
    'components/HTTP/Request/ServerRequest/IncomingRequest/ServerRequest.php',
    'components/HTTP/Router/System/PublicSurface/shortcuts.php',
    'components/HTTP/Security/System/Capabilities/Csrf/CsrfToken.php',
    'components/HTTP/Security/System/Capabilities/Csrf/CsrfTokenGenerator.php',
    'components/HTTP/Security/System/Capabilities/Csrf/CsrfVerifier.php',
    'components/HTTP/Session/System/Capabilities/Storage/NativeSessionStore.php',
    'components/HTTP/Session/System/PublicSurface/SessionScope.php',
    'components/HTTP/System/Flows/CreateRequestFromGlobals/CreateRequestFromGlobals.php',
    'components/HTTP/System/Flows/SendResponse/SendResponse.php',
    'components/Identity/Auth/System/Capabilities/Identity/Sessions/Runtime/NativeSessionStore.php',
    'components/Operations/Logging/System/Flows/WriteErrorLog/WriteErrorLog.php',
    'components/Presentation/View/System/PublicSurface/shortcuts.php',
    'framework/System/Capabilities/RuntimeSafety/StatelessBoundary/System/Capabilities/Enforcement/StatelessGuard.php',
    'framework/System/Flows/HandleRuntimeFailure/RenderRuntimeFailure.php',
    'framework/System/Flows/HandleRuntimeFailure/ReportRuntimeFailure.php',
];

$violations = [];

foreach ($scanRoots as $scanRoot) {
    $scanPath = $root . '/' . $scanRoot;

    if (! is_dir(filename: $scanPath)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        iterator: new RecursiveDirectoryIterator(directory: $scanPath, flags: FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if (! $file instanceof SplFileInfo) {
            continue;
        }

        if (! $file->isFile()) {
            continue;
        }

        if (strtolower(string: $file->getExtension()) !== 'php') {
            continue;
        }

        $realPath = $file->getRealPath();
        if ($realPath === false) {
            continue;
        }

        $relativePath = str_replace(search: '\\', replace: '/', subject: substr(string: $realPath, offset: strlen(string: $root) + 1));
        if (in_array(needle: $relativePath, haystack: $allowedFiles, strict: true)) {
            continue;
        }

        $content = file_get_contents(filename: $realPath);
        if ($content === false) {
            continue;
        }

        foreach (token_get_all(code: $content) as $token) {
            if (! is_array(value: $token)) {
                continue;
            }

            if ($token[0] !== T_VARIABLE) {
                continue;
            }

            foreach ($patterns as $pattern) {
                if ($token[1] === $pattern) {
                    $violations[] = sprintf('%s:%d uses %s', $relativePath, $token[2], $pattern);
                    break;
                }
            }
        }
    }
}

if ($violations !== []) {
    echo "Superglobal usage detected outside HTTP boundary:\n";
    foreach (array_unique(array: $violations) as $violation) {
        echo sprintf('  - %s%s', $violation, PHP_EOL);
    }

    exit(1);
}

echo "No unauthorized superglobal usage detected.\n";

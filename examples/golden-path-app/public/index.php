<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
use Avax\Framework\System\Configuration\BuildApplication\Builders\ApplicationBuilder;
use Avax\Framework\System\Flows\HandleIncomingHttp\HandleIncomingHttp;
use Avax\Framework\System\Flows\RunDoctor\RunDoctor;
use Avax\Framework\System\Foundation\Environment\EnvironmentName;
use Avax\Framework\System\Foundation\Paths\ProjectPath;
use Avax\Framework\System\Foundation\Time\SystemClock;
use Avax\Framework\System\PublicSurface\Avax;

// 1. Build the Application Configuration
$projectRealPath = realpath(__DIR__ . '/../');
if ($projectRealPath === false) {
    throw new RuntimeException('Could not resolve project path');
}
$builder = new ApplicationBuilder(
    projectPath       : new ProjectPath($projectRealPath),
    environmentName   : new EnvironmentName('development'),
    clock             : new SystemClock(),
    runDoctor         : new RunDoctor(),
    handleIncomingHttp: new HandleIncomingHttp(createHttpResponse: new CreateHttpResponse()),
    filesystem        : new Filesystem(),
    createHttpResponse: new CreateHttpResponse(),
);

// 2. Boot the Framework
$avax = Avax::boot($builder);

// 3. Handle a basic HTTP request (Golden Path)
$httpKernel = $avax->http();

// For the sake of the Golden Path App example, we mock a basic ServerRequest 
// since we haven't wired up a full ServerRequestFactory yet in this minimal scope,
// or we just output a basic response if we just want to prove the kernel boots.

// Since this is just to prove the kernel boots and HTTP kernel is reachable:
echo "AvaX Kernel Booted Successfully!\n";
echo "Runtime State: " . $avax->state()->runtimeName() . "\n";
echo "Registered Components: " . count($avax->components()->names()) . "\n";

// A formal HTTP response representation (Golden Path)
// $response = $httpKernel->handle($request);
// echo $response->getBody();

// 4. Reset state (Golden Path cleanup)
$resetReport = $avax->resetState();
echo "Reset Components: " . count($resetReport->resetComponents()) . "\n";

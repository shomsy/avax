<?php
$files = [
    'framework/System/Flows/HandleIncomingHttp/DispatchConfiguredRoute.php',
    'framework/System/Flows/RunApplication/RunApplication.php',
    'tests/Integration/HTTP/SecureRequest/SecureRequestHttpIntegrationTest.php',
    'tests/Unit/Components/HTTP/Dispatcher/DispatcherCapabilitiesTest.php'
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    if (! str_contains($content, 'SecureRequestInputBuilder')) {
        $content = str_replace('use Avax\Components\HTTP\Dispatcher\System\Capabilities\ArgumentResolution\ArgumentResolver;', "use Avax\Components\HTTP\Dispatcher\System\Capabilities\ArgumentResolution\ArgumentResolver;\nuse Avax\Components\HTTP\SecureRequest\System\Capabilities\ResolveSecureRequest\SecureRequestInputBuilder;", $content);
    }
    // Fix instantiation
    $content = preg_replace('/new ArgumentResolver\s*\(\s*typeResolvers:\s*\$[^)]+\)/', 'new ArgumentResolver(typeResolvers: $this->typeResolvers, inputBuilder: new SecureRequestInputBuilder())', $content);
    // For tests where it might be different
    $content = preg_replace('/new ArgumentResolver\s*\(\s*[^)]+\)/', 'new ArgumentResolver(typeResolvers: $typeResolvers, inputBuilder: new SecureRequestInputBuilder())', $content);
    
    file_put_contents($file, $content);
}

// Fix Parallelism test
$parallelTestFile = 'tests/Unit/Components/Operations/Parallelism/ParallelismProofTest.php';
$parallelTest     = file_get_contents($parallelTestFile);
if (! str_contains($parallelTest, 'use Avax\Components\Operations\Parallelism\System\PublicSurface\Parallel;')) {
    $parallelTest = str_replace('use Avax\Components\Operations\Parallelism\System\Foundation\ParallelResult;', "use Avax\Components\Operations\Parallelism\System\Foundation\ParallelResult;\nuse Avax\Components\Operations\Parallelism\System\PublicSurface\Parallel;", $parallelTest);
}
file_put_contents($parallelTestFile, $parallelTest);

// Fix AuthBuilder
$authBuilderFile = 'components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php';
$authBuilder      = file_get_contents($authBuilderFile);
$authBuilder      = str_replace('readProviderMetadata    :', 'readOidcProviderMetadata:', $authBuilder);
$authBuilder      = str_replace('readJsonWebKeySet       :', 'readOidcJsonWebKeySet   :', $authBuilder);
$authBuilder      = str_replace('readUserInfo            :', 'readOidcUserInfo        :', $authBuilder);
file_put_contents($authBuilderFile, $authBuilder);

echo "Final fixes applied.\n";

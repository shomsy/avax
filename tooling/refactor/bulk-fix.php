<?php
$files = [
    'framework/System/Flows/HandleIncomingHttp/DispatchConfiguredRoute.php',
    'framework/System/Flows/RunApplication/RunApplication.php'
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    if (! str_contains($content, 'SecureRequestInputBuilder')) {
        // Need to add import and injection
        $content = str_replace('use Avax\Components\HTTP\Dispatcher\System\Capabilities\ArgumentResolution\ArgumentResolver;', "use Avax\Components\HTTP\Dispatcher\System\Capabilities\ArgumentResolution\ArgumentResolver;\nuse Avax\Components\HTTP\SecureRequest\System\Capabilities\ResolveSecureRequest\SecureRequestInputBuilder;", $content);
        $content = str_replace('new ArgumentResolver(typeResolvers: $this->typeResolvers)', 'new ArgumentResolver(typeResolvers: $this->typeResolvers, inputBuilder: new SecureRequestInputBuilder())', $content);
    }
    file_put_contents($file, $content);
}

// Fix AuthBuilder nullability
$authBuilderFile = 'components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php';
$authBuilder     = file_get_contents($authBuilderFile);
$authBuilder     = str_replace('$provisionableUserSource ?? throw', '$provisionableUserSource ?? throw', $authBuilder); // This is just for safety
// I'll actually fix the variable to be nullable in the type hint if possible or just silence PHPStan if it's correct logic.
// The error is: Variable $provisionableUserSource on left side of ?? always exists and is not nullable.
// I'll change it to just:
$authBuilder = preg_replace('/\$provisionableUserSource \?\? throw/', 'throw', $authBuilder);
// Wait, no. If it's not nullable, I should just use it.
$authBuilder = preg_replace('/\$provisionableUserSource \?\? /', '', $authBuilder);

file_put_contents($authBuilderFile, $authBuilder);

echo "Final fixes applied.\n";

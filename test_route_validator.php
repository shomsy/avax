<?php

// Load DSL classes manually to avoid bootstrap issues
require_once __DIR__ . '/Foundation/Text/Text.php';
require_once __DIR__ . '/Foundation/Text/Pattern.php';
require_once __DIR__ . '/Foundation/Text/MatchResult.php';
require_once __DIR__ . '/Foundation/Text/RegexException.php';

// Load Router validation classes
require_once __DIR__ . '/Foundation/HTTP/Router/Validation/RouteValidator.php';
require_once __DIR__ . '/Foundation/HTTP/Router/Routing/RoutePathValidator.php';

// Test the DSL methods directly first
echo "🧪 Testing DSL Methods Directly:\n";

$testPath = '/users/{id}/posts/{slug}';
$text     = Avax\Text\Text::of(value: $testPath);

echo "Path: {$testPath}\n";
echo "Valid route path: " . (Avax\HTTP\Router\Validation\RouteValidator::containsValidRoutePathCharacters(path: $testPath) ? "✅ YES" : "❌ NO") . "\n";
echo "Route params: " . json_encode(Avax\HTTP\Router\Validation\RouteValidator::extractRouteParameters(path: $testPath)) . "\n";

echo "\n🔄 Testing RoutePathValidator Integration:\n";

// Load the validator class
require_once __DIR__ . '/Foundation/HTTP/Router/Routing/RoutePathValidator.php';

$validPaths = [
    '/users',
    '/users/{id}',
    '/users/{id}/posts/{slug}',
    '/api/v1/users/{userId}/posts/{postId}',
];

$invalidPaths = [
    'users',           // Missing leading slash
    '/users/{id',      // Unbalanced braces
    '/users/{123invalid}', // Invalid param name
];

echo "Testing VALID paths:\n";
foreach ($validPaths as $path) {
    try {
        Avax\HTTP\Router\Routing\RoutePathValidator::validate(path: $path);
        echo "✅ '{$path}' - PASSED\n";
    } catch (Exception $e) {
        echo "❌ '{$path}' - FAILED: " . $e->getMessage() . "\n";
    }
}

echo "\nTesting INVALID paths:\n";
foreach ($invalidPaths as $path) {
    try {
        Avax\HTTP\Router\Routing\RoutePathValidator::validate(path: $path);
        echo "❌ '{$path}' - SHOULD HAVE FAILED but passed\n";
    } catch (Exception $e) {
        echo "✅ '{$path}' - Correctly failed: " . $e->getMessage() . "\n";
    }
}

echo "\n🎉 RoutePathValidator DSL integration test completed!\n";
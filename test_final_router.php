<?php

// Test final Router DSL integration
echo "🧪 Final Router DSL Integration Test\n\n";

// Load all required files
require_once __DIR__ . '/Foundation/Text/Text.php';
require_once __DIR__ . '/Foundation/Text/Pattern.php';
require_once __DIR__ . '/Foundation/Text/MatchResult.php';
require_once __DIR__ . '/Foundation/Text/RegexException.php';
require_once __DIR__ . '/Foundation/HTTP/Router/Validation/RouteValidator.php';
require_once __DIR__ . '/Foundation/HTTP/Router/functions.php';

// Test DSL functionality directly first
echo "1. Testing RouteValidator directly:\n";
$path = '/users/{id}/posts/{slug}';
echo "   Path: {$path}\n";

try {
    $valid = Avax\HTTP\Router\Validation\RouteValidator::containsValidRoutePathCharacters($path);
    echo "   Valid chars: " . ($valid ? "✅ YES" : "❌ NO") . "\n";

    $params = Avax\HTTP\Router\Validation\RouteValidator::extractRouteParameters($path);
    echo "   Params: [" . implode(', ', $params) . "]\n";

    echo "   ✅ RouteValidator direct test PASSED\n";
} catch (Exception $e) {
    echo "   ❌ RouteValidator direct test FAILED: " . $e->getMessage() . "\n";
}

echo "\n2. Testing Router DSL functions:\n";

// Test route_valid function
echo "   route_valid('/users/{id}') -> " . (route_valid(path: '/users/{id}') ? "✅ VALID" : "❌ INVALID") . "\n";
echo "   route_valid('users') -> " . (route_valid(path: 'users') ? "❌ SHOULD BE INVALID" : "✅ INVALID") . "\n";

// Test route_params function
$params = route_params(path: '/api/{version}/users/{userId}');
echo "   route_params('/api/{version}/users/{userId}') -> [" . implode(', ', $params) . "]\n";

// Test route_pattern function
$pattern = route_pattern(template: '/users/{id}', constraints: ['id' => '\d+']);
echo "   route_pattern('/users/{id}', ['id' => '\d+']) -> {$pattern}\n";

echo "\n🎉 Router DSL Integration Test Complete!\n";
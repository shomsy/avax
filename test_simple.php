<?php

// Simple test for RouteValidator
require_once __DIR__ . '/Foundation/Text/Text.php';
require_once __DIR__ . '/Foundation/Text/Pattern.php';
require_once __DIR__ . '/Foundation/Text/MatchResult.php';
require_once __DIR__ . '/Foundation/Text/RegexException.php';
require_once __DIR__ . '/Foundation/HTTP/Router/Validation/RouteValidator.php';

echo "Testing RouteValidator...\n";

$path = '/users/{id}/posts/{slug}';
echo "Path: {$path}\n";

try {
    $result = Avax\HTTP\Router\Validation\RouteValidator::containsValidRoutePathCharacters(path: $path);
    echo "Valid chars: " . ($result ? "YES" : "NO") . "\n";

    $params = Avax\HTTP\Router\Validation\RouteValidator::extractRouteParameters(path: $path);
    echo "Params: " . json_encode($params) . "\n";

    echo "✅ SUCCESS!\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
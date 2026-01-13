<?php

// Test Router DSL Helper Layer
require_once __DIR__ . '/Foundation/Text/Text.php';
require_once __DIR__ . '/Foundation/Text/Pattern.php';
require_once __DIR__ . '/Foundation/Text/MatchResult.php';
require_once __DIR__ . '/Foundation/Text/RegexException.php';
require_once __DIR__ . '/Foundation/HTTP/Router/Validation/RouteValidator.php';
require_once __DIR__ . '/Foundation/HTTP/Router/functions.php';

echo "🧪 Testing Router DSL Helper Layer\n\n";

// Test route validation
echo "Route Validation:\n";
$validPaths   = ['/users', '/users/{id}', '/api/v1/users/{userId}/posts/{postId}'];
$invalidPaths = ['users', '/users/{id', '/users/{123invalid}'];

foreach ($validPaths as $path) {
    $valid = route_valid(path: $path);
    echo "✅ '{$path}' -> " . ($valid ? "VALID" : "INVALID") . "\n";
}

foreach ($invalidPaths as $path) {
    $valid = route_valid(path: $path);
    echo "❌ '{$path}' -> " . ($valid ? "VALID" : "INVALID") . "\n";
}

echo "\nRoute Parameter Extraction:\n";
$paths = ['/users/{id}', '/api/{version}/users/{userId}/posts/{postId}'];
foreach ($paths as $path) {
    $params = route_params(path: $path);
    echo "📋 '{$path}' -> [" . implode(', ', $params) . "]\n";
}

echo "\nRoute Pattern Compilation:\n";
$templates = [
    '/users/{id}'    => [],
    '/blog/{slug?}'  => [],
    '/files/{path*}' => [],
    '/users/{id}'    => ['id' => '\d+'],
];

foreach ($templates as $template => $constraints) {
    $pattern = route_pattern(template: $template, constraints: $constraints);
    echo "🔧 '{$template}' -> {$pattern}\n";
}

echo "\n🎉 Router DSL Helper Layer test completed!\n";
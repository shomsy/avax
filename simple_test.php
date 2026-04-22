<?php

// Simple test without bootstrap
require_once 'Foundation/Text/Text.php';
require_once 'Foundation/Text/Pattern.php';
require_once 'Foundation/Text/MatchResult.php';
require_once 'Foundation/Text/RegexException.php';

echo "Testing DSL...\n";

$text = Avax\Text\Text::of(value: 'test@example.com');
echo "Email: " . ($text->isValidEmail() ? "PASS" : "FAIL") . "\n";

$text2 = Avax\Text\Text::of(value: '123abc');
echo "Alphanumeric: " . ($text2->containsOnlyAlphanumeric() ? "PASS" : "FAIL") . "\n";

$text3 = Avax\Text\Text::of(value: '/users/{id}/posts/{slug}');
echo "Route params: " . json_encode($text3->extractRouteParameters()) . "\n";
echo "Valid route path: " . ($text3->validateRoutePath() ? "PASS" : "FAIL") . "\n";

echo "All tests completed!\n";
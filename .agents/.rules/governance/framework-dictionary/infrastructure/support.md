# Term
Support

# Classification
Testing/Helper Infrastructure

# Purpose
Provides test helpers, fixtures, factories, and shared test utilities that support the testing infrastructure. Support code in this context exists solely to make tests easier to write, more readable, and more maintainable — it is not production behavior.

# Why Allowed
"Support" is a standard convention in testing frameworks across languages for test utilities and helpers that reduce duplication and improve test readability. PHPUnit ecosystems use test support traits and base test classes. Symfony has test support classes for functional testing, kernel testing, and browser testing. Laravel provides test support through TestCase base classes, factory definitions, and testing helpers. Ruby on Rails has `test/support` for shared test configuration and helpers. Jest and testing libraries across JavaScript ecosystems use support files for test setup, mocks, and shared fixtures. In test contexts, "Support" has a clear, narrow meaning: code that exists to make tests better, not code that exists to make production work. Support code is never on the production path — it is loaded only during test execution, it is not autoloaded in production, and it does not affect runtime behavior. This is fundamentally different from a production "Support" folder, which would be a forbidden generic utility bucket.

# Allowed Contexts
- Test helpers and shared test utilities (base test classes, test traits)
- Test fixtures and test data factories
- Test factories for model and entity creation
- Shared test configuration and environment setup
- Mock builders and stub generators for testing
- Test assertions and custom test matchers

# Forbidden Misuse
- As a production code support folder or utility bucket
- As a generic helper location outside of test contexts
- Naming production classes with "Support" suffix
- Creating a Support/ folder in production code paths
- Using "Support" to describe infrastructure that serves production code

# Ecosystem References
- https://phpunit.de/manual/current/en/test-doubles.html
- https://symfony.com/doc/current/testing.html
- https://laravel.com/docs/testing#generating-urls-during-testing
- https://guides.rubyonrails.org/testing.html

# Allowed Patterns
- TestSupport
- SupportTestCase
- FactorySupport
- DatabaseTestSupport
- HttpTestSupport
- MockSupport

# Forbidden Patterns
- Support/ (in production code paths)
- SupportManager (managers are forbidden; support is helper infrastructure, not management)
- AppSupport (vague — support for what?)
- Support/Support (recursive, meaningless)

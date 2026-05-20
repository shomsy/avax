# TODO-006 Slice D Low-Level Design

## Class to Create: `BuildAvaxEngine`

- **Namespace**: `Avax\Framework\System\Configuration\BuildApplication\Builders`
- **Location**: `framework/System/Configuration/BuildApplication/Builders/BuildAvaxEngine.php`
- **Methods**:
  - `public static function createApp(string $environment): App`
    - Contains the entire `App` object-graph assembly formerly in `Avax::create()`.
  - `public static function boot(ApplicationBuilder $builder): Avax`
    - Contains the entire `Avax` engine assembly formerly in `Avax::bootInternal()`.

## Class to Modify: `Avax`

- **Location**: `framework/System/PublicSurface/Avax.php`
- **Changes**:
  - Update `use` statements to import `BuildAvaxEngine`.
  - Rewrite `create()`:
    ```php
    public static function create(string $environment = 'production'): App
    {
        return BuildAvaxEngine::createApp(environment: $environment);
    }
    ```
  - Rewrite `bootInternal()`:
    ```php
    private static function bootInternal(ApplicationBuilder $builder): self
    {
        return BuildAvaxEngine::boot(builder: $builder);
    }
    ```

## Tests to Update: `V4AppDoesNotDuplicateComponentsTest`

- **Location**: `tests/Contract/V4Architecture/V4AppDoesNotDuplicateComponentsTest.php`
- **Changes**:
  - Load `framework/System/PublicSurface/Avax.php` and `framework/System/Configuration/BuildApplication/Builders/BuildAvaxEngine.php`.
  - Add `testAvaxDelegatesAssemblyToConfiguration()`:
    - Assert that `Avax.php` contains no direct instantiation of `CreateHttpResponse`, `CreateRequestFromGlobals`, `BootApplication`, `BuildApplicationState`, `HttpKernel`, `ConsoleKernel`, `RuntimeKernel`, or `ResetApplicationState`.
    - Assert that `BuildAvaxEngine.php` contains these direct instantiations.

# Term
Kernel

# Classification
Framework Core / Bootstrap Component

# Purpose
Acts as the central bootstrap and dispatch engine for a framework, responsible for loading configuration, registering providers, processing the HTTP or console lifecycle, and coordinating the application's core runtime behavior.

# Why Allowed
Kernel is a foundational term in PHP frameworks. Laravel defines both an HTTP Kernel and a Console Kernel, each responsible for bootstrapping the application, loading middleware, and dispatching requests or commands. Symfony uses a similar concept in its HttpKernel component, which handles the core request-response lifecycle. The kernel is the application's central coordinator — it is not a generic class but a well-defined framework entrypoint with clear responsibilities: bootstrap, dispatch, and terminate.

# Allowed Contexts
- HTTP request lifecycle coordination (HTTP Kernel)
- Console command lifecycle coordination (Console Kernel)
- Framework bootstrap, provider loading, and configuration assembly
- Middleware pipeline registration and execution
- Application termination and cleanup
- Long-lived worker boot and warm-start sequences

# Forbidden Misuse
- As a place to put business logic or application flows
- As a generic "core" folder for miscellaneous code
- Kernel classes that do not actually bootstrap or dispatch anything
- Kernels that leak framework internals to application code

# Ecosystem References
- https://laravel.com/docs/lifecycle
- https://symfony.com/doc/current/components/http_kernel.html
- https://laravel.com/docs/artisan#the-kernel

# Allowed Patterns
- HttpKernel
- ConsoleKernel
- FrameworkKernel
- WorkerKernel

# Forbidden Patterns
- CoreKernel (redundant — kernel already implies core)
- ApplicationKernel (ambiguous — which lifecycle?)
- Kernel/Kernel (recursive, meaningless)

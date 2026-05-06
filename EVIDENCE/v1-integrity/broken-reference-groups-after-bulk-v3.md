PASS 1: Parsing 3275 files for definitions...
  500 / 3275
  1000 / 3275
  1500 / 3275
  2000 / 3275
  2500 / 3275
  3000 / 3275
PASS 1 done. Defined: 3309
PASS 2: Parsing 3275 files for references...
  500 / 3275
  1000 / 3275
  1500 / 3275
  2000 / 3275
  2500 / 3275
  3000 / 3275

=== BROKEN REFERENCES AUDIT REPORT ===

MISSING: ActiveConditionalGateway  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/ResolveService/ConditionalCompositionSmokeTest.php:44  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/ResolveService/ConditionalCompositionSmokeTest.php:77  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/ResolveService/ConditionalCompositionSmokeTest.php:119  (class-const-fetch)

MISSING: Avax\Components\Application\Cache\CompiledCache  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Cache/tests/Unit/Cache/PublicCacheClassesAutoloadTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Cache/tests/Unit/Cache/PublicCacheClassesAutoloadTest.php:24  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Cache/tests/Unit/Cache/Providers/CacheServiceProviderTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Cache/tests/Unit/Cache/Providers/CacheServiceProviderTest.php:78  (static-call)
  - /home/shomsy/projects/avax/components/Application/Cache/tests/Unit/Cache/Providers/CacheServiceProviderTest.php:98  (static-call)
  - /home/shomsy/projects/avax/components/Application/Cache/tests/Unit/Cache/Providers/RegisterCacheDependenciesTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Cache/tests/Unit/Cache/Providers/RegisterCacheDependenciesTest.php:81  (static-call)
  - /home/shomsy/projects/avax/components/Application/Cache/tests/Unit/Cache/Providers/RegisterCacheDependenciesTest.php:96  (static-call)
  - /home/shomsy/projects/avax/components/Application/Cache/tests/Unit/Cache/PublicSurface/CacheReadRoutingTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Cache/tests/Unit/Cache/PublicSurface/CacheReadRoutingTest.php:98  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Cache/tests/Unit/Cache/PublicSurface/CacheReadRoutingTest.php:108  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Cache/tests/Unit/Cache/PublicSurface/CacheReadRoutingTest.php:120  (static-call)
  - /home/shomsy/projects/avax/components/Application/Cache/tests/Unit/Cache/PublicSurface/CacheReadRoutingTest.php:127  (static-call)
  - /home/shomsy/projects/avax/components/Application/Cache/tests/Unit/Cache/PublicSurface/CacheReadRoutingTest.php:212  (static-call)
  - /home/shomsy/projects/avax/components/Application/Cache/tests/Unit/Cache/PublicSurface/CacheReadRoutingTest.php:226  (static-call)
  - /home/shomsy/projects/avax/components/Application/Cache/tests/Unit/Cache/NoTimeFunctionInSystemTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Cache/tests/Unit/Cache/NoTimeFunctionInSystemTest.php:53  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Cache/Cache.php:86  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Components/Application/Cache/Unit/Cache/PublicCacheClassesAutoloadTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Components/Application/Cache/Unit/Cache/PublicCacheClassesAutoloadTest.php:24  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Unit/Components/Application/Cache/Unit/Cache/Providers/CacheServiceProviderTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Components/Application/Cache/Unit/Cache/Providers/CacheServiceProviderTest.php:82  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Components/Application/Cache/Unit/Cache/Providers/CacheServiceProviderTest.php:102  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Components/Application/Cache/Unit/Cache/PublicSurface/CacheReadRoutingTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Components/Application/Cache/Unit/Cache/PublicSurface/CacheReadRoutingTest.php:95  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Unit/Components/Application/Cache/Unit/Cache/PublicSurface/CacheReadRoutingTest.php:105  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Unit/Components/Application/Cache/Unit/Cache/PublicSurface/CacheReadRoutingTest.php:117  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Components/Application/Cache/Unit/Cache/PublicSurface/CacheReadRoutingTest.php:124  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Components/Application/Cache/Unit/Cache/PublicSurface/CacheReadRoutingTest.php:208  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Components/Application/Cache/Unit/Cache/PublicSurface/CacheReadRoutingTest.php:221  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Components/Application/Cache/Unit/Cache/NoTimeFunctionInSystemTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Components/Application/Cache/Unit/Cache/NoTimeFunctionInSystemTest.php:49  (class-const-fetch)

MISSING: Avax\Components\Application\Cache\Examples\SystemClock  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Cache/examples/multi-tier.php:13  (new)

MISSING: Avax\Components\Application\Cache\System\Cache  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Cache/tests/Unit/Cache/PublicCacheClassesAutoloadTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Cache/tests/Unit/Cache/PublicCacheClassesAutoloadTest.php:40  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Unit/Components/Application/Cache/Unit/Cache/PublicCacheClassesAutoloadTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Components/Application/Cache/Unit/Cache/PublicCacheClassesAutoloadTest.php:40  (class-const-fetch)

MISSING: Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\CacheOperation  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Cache/System/Capabilities/Observability/ObserveCache/CacheTrace.php:9  (param-type)

MISSING: Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreInterface  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Cache/System/Capabilities/Storage/StoreCachedValues/RedisCacheStore.php:21  (implements)

MISSING: Avax\Components\Application\Cache\tests\Unit\Cache\Providers\CacheNotConfigured  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Cache/tests/Unit/Cache/Providers/CacheServiceProviderTest.php:27  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Cache/tests/Unit/Cache/Providers/CacheServiceProviderTest.php:71  (class-const-fetch)

MISSING: Avax\Components\Application\Config\Architecture\DDD\AppPath  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Config/System/PublicSurface/shortcuts.php:9  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Config/System/PublicSurface/shortcuts.php:31  (static-call)
  - /home/shomsy/projects/avax/components/Application/Config/functions.php:5  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Config/functions.php:33  (static-call)

MISSING: Avax\Components\Application\Config\Service\Config  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Config/System/PublicSurface/shortcuts.php:10  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Config/System/PublicSurface/shortcuts.php:18  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Config/functions.php:6  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Config/functions.php:19  (class-const-fetch)

MISSING: Avax\Components\Application\Config\System\Capabilities\Architecture\AppPath  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Config/System/Flows/LoadConfiguration/LoadConfiguration.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Config/System/Flows/LoadConfiguration/LoadConfiguration.php:27  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Config/System/Flows/LoadConfiguration/LoadConfiguration.php:28  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Config/System/Flows/LoadConfiguration/LoadConfiguration.php:29  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Config/System/Flows/LoadConfiguration/LoadConfiguration.php:30  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Config/System/Flows/LoadConfiguration/LoadConfiguration.php:31  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Config/System/Flows/LoadConfiguration/LoadConfiguration.php:32  (class-const-fetch)

MISSING: Avax\Components\Application\Config\System\Capabilities\EnvironmentAwareness\LocalPolicy  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Config/System/Capabilities/EnvironmentAwareness/EnvironmentConfig.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Config/System/Capabilities/EnvironmentAwareness/Environment.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Config/System/Capabilities/EnvironmentAwareness/Environment.php:81  (new)
  - /home/shomsy/projects/avax/components/Application/Config/System/Capabilities/EnvironmentAwareness/Environment.php:85  (new)

MISSING: Avax\Components\Application\Container\BindingBuilderInterface  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/PublicSurface/ContainerPublicSurfaceTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/PublicSurface/ContainerPublicSurfaceTest.php:22  (class-const-fetch)

MISSING: Avax\Components\Application\Container\ContextBuilderInterface  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/PublicSurface/ContainerPublicSurfaceTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/PublicSurface/ContainerPublicSurfaceTest.php:26  (class-const-fetch)

MISSING: Avax\Components\Application\Container\Core\AppFactory  [MINOR]
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:75  (static-call)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:268  (static-call)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:236  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/HttpApplicationTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/HttpApplicationTest.php:49  (static-call)

MISSING: Avax\Components\Application\Container\Core\Capabilities\Binding\Binders\Binder  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Configuration/RegisterFilesystem.php:7  (use-statement)

MISSING: Avax\Components\Application\Container\Core\Capabilities\Binding\BindingRepository  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Configuration/RegisterFilesystem.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Configuration/RegisterFilesystem.php:16  (param-type)

MISSING: Avax\Components\Application\Container\Core\Exceptions\FoundationContainerException  [MINOR]
  - /home/shomsy/projects/avax/components/Presentation/View/TemplateEngine.php:14  (use-statement)

MISSING: Avax\Components\Application\Container\DependencyInjection\Capability\Invocation\CallableInvocation\InvocationContext  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Invocation/CallableInvocation/InvocationExecutorTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Invocation/CallableInvocation/InvocationExecutorTest.php:35  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Invocation/CallableInvocation/InvocationExecutorTest.php:65  (new)

MISSING: Avax\Components\Application\Container\DependencyInjection\Capability\Invocation\CallableInvocation\InvocationExecutor  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Invocation/CallableInvocation/InvocationExecutorTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Invocation/CallableInvocation/InvocationExecutorTest.php:29  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Invocation/CallableInvocation/InvocationExecutorTest.php:59  (new)

MISSING: Avax\Components\Application\Container\DependencyInjection\Capability\Invocation\InvokeAction  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:14  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:86  (new)

MISSING: Avax\Components\Application\Container\DependencyInjection\Capability\Prototypes\Analyze\PrototypeAnalyzer  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:17  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:66  (new)

MISSING: Avax\Components\Application\Container\DependencyInjection\Capability\Prototypes\Analyze\ReflectionTypeAnalyzer  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:18  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:66  (new)

MISSING: Avax\Components\Application\Container\DependencyInjection\Capability\Prototypes\Cache\PrototypeCache  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:19  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:65  (class-const-fetch)

MISSING: Avax\Components\Application\Container\DependencyInjection\Capability\Prototypes\Factory\ServicePrototypeFactory  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:20  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:67  (new)

MISSING: Avax\Components\Application\Container\DependencyInjection\Capability\Prototypes\Model\PropertyPrototype  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/Properties/PropertyInjectorTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/Properties/PropertyInjectorTest.php:24  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/Properties/PropertyInjectorTest.php:47  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/Properties/PropertyInjectorTest.php:64  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/Properties/PropertyInjectorTest.php:81  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/Properties/PropertyInjectorTest.php:97  (new)

MISSING: Avax\Components\Application\Container\DependencyInjection\Capability\Prototypes\Model\ServicePrototype  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/var/cache/stdClass.php:5  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/var/cache/stdClass.php:7  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Container/var/cache/Avax_Logging_LoggerFactory.php:5  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/var/cache/Avax_Logging_LoggerFactory.php:8  (static-call)

MISSING: Avax\Components\Application\Container\DependencyInjection\Capability\Providers\Runtime\Http\MiddlewareBaseRegisterDependency  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/HttpApplicationTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/HttpApplicationTest.php:50  (class-const-fetch)

MISSING: Avax\Components\Application\Container\DependencyInjection\Capability\Providers\Runtime\Http\RouterBaseRegisterDependency  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/HttpApplicationTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/HttpApplicationTest.php:50  (class-const-fetch)

MISSING: Avax\Components\Application\Container\DependencyInjection\Capability\Scopes\ScopeManager  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:25  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:62  (new)

MISSING: Avax\Components\Application\Container\DependencyInjection\Capability\Scopes\ScopeRegistry  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:26  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:61  (new)

MISSING: Avax\Components\Application\Container\DependencyInjection\Configuration\ContainerBuilder  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineTraceTest.php:13  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineTraceTest.php:22  (new)

MISSING: Avax\Components\Application\Container\DependencyInjection\Configuration\ContainerConfig  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/ContainerConfigTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/ContainerConfigTest.php:14  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/ContainerConfigTest.php:21  (new)

MISSING: Avax\Components\Application\Container\DependencyInjection\Configuration\KernelConfig  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:27  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:88  (new)

MISSING: Avax\Components\Application\Container\Providers\Auth\AuthenticationBaseRegisterDependency  [MINOR]
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:62  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:254  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:222  (class-const-fetch)

MISSING: Avax\Components\Application\Container\Providers\Auth\SecurityBaseRegisterDependency  [MINOR]
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:63  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:255  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:223  (class-const-fetch)

MISSING: Avax\Components\Application\Container\Providers\Core\ConfigurationBaseRegisterDependency  [MINOR]
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:59  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:251  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:219  (class-const-fetch)

MISSING: Avax\Components\Application\Container\Providers\Core\FilesystemBaseRegisterDependency  [MINOR]
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:60  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:252  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:220  (class-const-fetch)

MISSING: Avax\Components\Application\Container\Providers\Core\LoggingBaseRegisterDependency  [MINOR]
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:61  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:253  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:221  (class-const-fetch)

MISSING: Avax\Components\Application\Container\Providers\Database\RegisterDatabaseDependencies  [MINOR]
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:13  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:64  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:256  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:13  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:224  (class-const-fetch)

MISSING: Avax\Components\Application\Container\Providers\HTTP\HTTPBaseRegisterDependency  [MINOR]
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:14  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:65  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:257  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:14  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:225  (class-const-fetch)

MISSING: Avax\Components\Application\Container\Providers\HTTP\HttpClientBaseRegisterDependency  [MINOR]
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:15  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:70  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:262  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:15  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:230  (class-const-fetch)

MISSING: Avax\Components\Application\Container\Providers\HTTP\MiddlewareBaseRegisterDependency  [MINOR]
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:16  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:66  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:258  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:16  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:226  (class-const-fetch)

MISSING: Avax\Components\Application\Container\Providers\HTTP\RouterBaseRegisterDependency  [MINOR]
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:17  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:67  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:259  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:17  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:227  (class-const-fetch)

MISSING: Avax\Components\Application\Container\Providers\HTTP\SessionBaseRegisterDependency  [MINOR]
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:18  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:68  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:260  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:18  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:228  (class-const-fetch)

MISSING: Avax\Components\Application\Container\Providers\HTTP\ViewBaseRegisterDependency  [MINOR]
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:19  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:69  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:261  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:19  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:229  (class-const-fetch)

MISSING: Avax\Components\Application\Container\Providers\ServiceProvider  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Cache/Providers/CacheServiceProvider.php:15  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Cache/Providers/CacheServiceProvider.php:17  (extends)

MISSING: Avax\Components\Application\Container\ScopeManagerInterface  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/PublicSurface/ContainerPublicSurfaceTest.php:13  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/PublicSurface/ContainerPublicSurfaceTest.php:34  (class-const-fetch)

MISSING: Avax\Components\Application\Container\System\Capabilities\CheckResolutionPolicy  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Policies/CheckResolutionPolicyTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Policies/CheckResolutionPolicyTest.php:19  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Policies/CheckResolutionPolicyTest.php:31  (new)

MISSING: Avax\Components\Application\Container\System\Capabilities\ContainerPolicy  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Policies/CheckResolutionPolicyTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Policies/CheckResolutionPolicyTest.php:20  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Policies/CheckResolutionPolicyTest.php:32  (new)

MISSING: Avax\Components\Application\Container\System\Capabilities\Decisions\ResolutionAllowed  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Policies/CheckResolutionPolicyTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Policies/CheckResolutionPolicyTest.php:37  (class-const-fetch)

MISSING: Avax\Components\Application\Container\System\Capabilities\Decisions\ResolutionBlocked  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Policies/CheckResolutionPolicyTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Policies/CheckResolutionPolicyTest.php:25  (class-const-fetch)

MISSING: Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DefinitionStore  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:34  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:59  (new)

MISSING: Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\ServiceDefinition  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:49  (new)

MISSING: Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability\Metrics\CollectMetrics  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:15  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:76  (new)

MISSING: Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability\Timeline\ResolutionTimeline  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:16  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:63  (new)

MISSING: Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability\Trace\ResolutionTrace  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineTraceTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineTraceTest.php:26  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineTraceTest.php:28  (param-type)

MISSING: Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability\Trace\TraceObserverInterface  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineTraceTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineTraceTest.php:25  (implements)

MISSING: Avax\Components\Application\Container\System\Capabilities\Execution\Injection\InjectDependencies  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:79  (new)

MISSING: Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Methods\MethodInjector  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:82  (new)

MISSING: Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Parameters\ResolveMethodParameters  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:83  (new)

MISSING: Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Properties\PropertyInjector  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:13  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:78  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/Properties/PropertyInjectorTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/Properties/PropertyInjectorTest.php:23  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/Properties/PropertyInjectorTest.php:46  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/Properties/PropertyInjectorTest.php:63  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/Properties/PropertyInjectorTest.php:80  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/Properties/PropertyInjectorTest.php:96  (new)

MISSING: Avax\Components\Application\Container\System\Capabilities\Providers\BaseRegisterDependency  [CRITICAL]
  - /home/shomsy/projects/avax/components/Identity/Auth/System/Configuration/RegisterAuthDependencies.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Auth/System/Configuration/RegisterAuthDependencies.php:29  (extends)
  - /home/shomsy/projects/avax/components/Application/Cache/System/Configuration/CacheRegistrar.php:16  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Cache/System/Configuration/CacheRegistrar.php:19  (extends)
  - /home/shomsy/projects/avax/components/Application/Cache/System/Configuration/RegisterCacheDependencies.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Cache/System/Configuration/RegisterCacheDependencies.php:17  (extends)

MISSING: Avax\Components\Application\Container\System\Capabilities\Resolution\Contracts\ContainerRuntimeInterface  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Invocation/CallableInvocation/InvocationExecutorTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Invocation/CallableInvocation/InvocationExecutorTest.php:22  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Invocation/CallableInvocation/InvocationExecutorTest.php:47  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/Properties/PropertyInjectorTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/Properties/PropertyInjectorTest.php:40  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/PublicSurface/ContainerPublicSurfaceTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/PublicSurface/ContainerPublicSurfaceTest.php:46  (class-const-fetch)

MISSING: Avax\Components\Application\Container\System\Capabilities\Resolution\Engine\DependencyResolver  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:21  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:69  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Invocation/CallableInvocation/InvocationExecutorTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Invocation/CallableInvocation/InvocationExecutorTest.php:31  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Invocation/CallableInvocation/InvocationExecutorTest.php:61  (new)

MISSING: Avax\Components\Application\Container\System\Capabilities\Resolution\Engine\Instantiator  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:22  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:70  (new)

MISSING: Avax\Components\Application\Container\System\Capabilities\Resolution\Engine\ResolutionEngine  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineTraceTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineTraceTest.php:45  (return-type)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:23  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:71  (new)

MISSING: Avax\Components\Application\Container\System\Capabilities\Resolution\Errors\ResolutionException  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/Properties/PropertyInjectorTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/Properties/PropertyInjectorTest.php:84  (class-const-fetch)

MISSING: Avax\Components\Application\Container\System\Capabilities\Resolution\Kernel\ContainerKernel  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineTraceTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineTraceTest.php:52  (class-const-fetch)

MISSING: Avax\Components\Application\Container\System\Capabilities\Resolution\Kernel\KernelContext  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineTraceTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineTraceTest.php:34  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Steps/CollectDiagnosticsStepTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Steps/CollectDiagnosticsStepTest.php:23  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Invocation/CallableInvocation/InvocationExecutorTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Invocation/CallableInvocation/InvocationExecutorTest.php:21  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Invocation/CallableInvocation/InvocationExecutorTest.php:46  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/Properties/PropertyInjectorTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/Properties/PropertyInjectorTest.php:25  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/Properties/PropertyInjectorTest.php:48  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/Properties/PropertyInjectorTest.php:65  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/Properties/PropertyInjectorTest.php:82  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/Properties/PropertyInjectorTest.php:98  (new)

MISSING: Avax\Components\Application\Container\System\Capabilities\Resolution\Kernel\RuntimeContainer  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:24  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:100  (new)

MISSING: Avax\Components\Application\Container\System\Capabilities\Resolution\Pipeline\Events\StepStarted  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Steps/CollectDiagnosticsStepTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Steps/CollectDiagnosticsStepTest.php:28  (new)

MISSING: Avax\Components\Application\Container\System\Capabilities\Resolution\Pipeline\Events\StepSucceeded  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Steps/CollectDiagnosticsStepTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Steps/CollectDiagnosticsStepTest.php:35  (new)

MISSING: Avax\Components\Application\Container\System\Capabilities\Resolution\Pipeline\Steps\CollectDiagnosticsStep  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Steps/CollectDiagnosticsStepTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Steps/CollectDiagnosticsStepTest.php:21  (new)

MISSING: Avax\Components\Application\Container\System\Capabilities\Resolution\Pipeline\Strategies\ResolutionState  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStateMachineTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStateMachineTest.php:18  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStateMachineTest.php:20  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStateMachineTest.php:28  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStateMachineTest.php:36  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStateMachineTest.php:43  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStateMachineTest.php:44  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStateMachineTest.php:45  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStateMachineTest.php:48  (class-const-fetch)

MISSING: Avax\Components\Application\Container\System\Capabilities\Resolution\Pipeline\Strategies\ResolutionStateMachine  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStateMachineTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStateMachineTest.php:16  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStateMachineTest.php:25  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStateMachineTest.php:33  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStateMachineTest.php:41  (new)

MISSING: Avax\Components\Application\Container\System\Capabilities\Resolution\Pipeline\Telemetry\StepTelemetryRecorder  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Steps/CollectDiagnosticsStepTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Steps/CollectDiagnosticsStepTest.php:20  (new)

MISSING: Avax\Components\Application\Container\System\Capabilities\Runtime\LazyProxy  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/System/ContainerInterface.php:14  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Container/System/ContainerInterface.php:281  (return-type)
  - /home/shomsy/projects/avax/components/Application/Container/System/Foundation/DIContainer.php:21  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Container/System/Foundation/DIContainer.php:493  (return-type)
  - /home/shomsy/projects/avax/components/Application/Container/System/ContextContainer.php:22  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Container/System/ContextContainer.php:408  (return-type)

MISSING: Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\Lifetimes\SharedLifetime  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistry.php:11  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistry.php:183  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistry.php:195  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistry.php:204  (class-const-fetch)

MISSING: Avax\Components\Application\Container\System\Capabilities\StrictResolutionPolicy  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Policies/CheckResolutionPolicyTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Policies/CheckResolutionPolicyTest.php:20  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Policies/CheckResolutionPolicyTest.php:32  (new)

MISSING: Avax\Components\Application\Container\System\ContainerFacade  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/functions.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Container/functions.php:19  (static-call)

MISSING: Avax\Components\Application\Container\System\Flows\RegisterDependencies\RegisterDependencies  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Container/System/Foundation/DIContainer.php:31  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Container/System/Foundation/DIContainer.php:117  (return-type)
  - /home/shomsy/projects/avax/components/Application/Container/System/Foundation/DIContainer.php:119  (new)

MISSING: Avax\Components\Application\Container\System\Foundation\Container  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Container/System/ContextContainer.php:28  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Container/System/ContextContainer.php:42  (constructor-param)

MISSING: Avax\Components\Application\Container\System\Foundation\ContextContainer  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Container/System/Foundation/DIContainer.php:509  (new)
  - /home/shomsy/projects/avax/components/Application/Container/System/Foundation/DIContainer.php:536  (new)

MISSING: Avax\Components\Application\Container\System\Foundation\DIContainerInterface  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/functions.php:5  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Container/functions.php:13  (param-type)
  - /home/shomsy/projects/avax/components/Application/Container/functions.php:13  (return-type)

MISSING: Avax\Components\Application\Container\System\Foundation\Ids\IdGenerator  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Foundation/FoundationSmokeTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Foundation/FoundationSmokeTest.php:11  (new)

MISSING: Avax\Components\Application\Facade\System\Foundation\BaseFacade  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Facade/System/Foundation/FacadeProvider.php:21  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Facade/System/Foundation/FacadeProvider.php:22  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Facade/System/Foundation/FacadeProvider.php:32  (static-call)
  - /home/shomsy/projects/avax/components/Application/Facade/System/Foundation/FacadeProvider.php:47  (static-call)
  - /home/shomsy/projects/avax/components/Application/Facade/System/PublicSurface/StorageFacade.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Facade/System/PublicSurface/StorageFacade.php:10  (extends)
  - /home/shomsy/projects/avax/components/Application/Facade/System/PublicSurface/Storage.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Facade/System/PublicSurface/Storage.php:10  (extends)
  - /home/shomsy/projects/avax/components/Application/Facade/System/PublicSurface/Request.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Facade/System/PublicSurface/Request.php:10  (extends)
  - /home/shomsy/projects/avax/components/Application/Facade/System/PublicSurface/RequestFacade.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Facade/System/PublicSurface/RequestFacade.php:10  (extends)
  - /home/shomsy/projects/avax/components/Application/Facade/System/PublicSurface/Auth.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Facade/System/PublicSurface/Auth.php:10  (extends)
  - /home/shomsy/projects/avax/components/Application/Facade/System/PublicSurface/SessionFacade.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Facade/System/PublicSurface/SessionFacade.php:10  (extends)
  - /home/shomsy/projects/avax/components/Application/Facade/System/PublicSurface/Route.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Facade/System/PublicSurface/Route.php:10  (extends)
  - /home/shomsy/projects/avax/components/Application/Facade/System/PublicSurface/Session.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Facade/System/PublicSurface/Session.php:10  (extends)

MISSING: Avax\Components\Application\Filesystem\Configuration\FilesystemConfig  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Capabilities/Disks/ResolveDisk.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Capabilities/Disks/ResolveDisk.php:13  (constructor-param)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Capabilities/Disks/ResolveDisk.php:13  (new)

MISSING: Avax\Components\Application\Filesystem\Directories\ClearDirectory  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:98  (new)

MISSING: Avax\Components\Application\Filesystem\Directories\CreateDirectory  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:88  (new)

MISSING: Avax\Components\Application\Filesystem\Directories\DeleteDirectory  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:9  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:93  (new)

MISSING: Avax\Components\Application\Filesystem\Directories\EnsureDirectoryExists  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:10  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:78  (new)

MISSING: Avax\Components\Application\Filesystem\Directories\EnsureDirectoryIsWritable  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:11  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:83  (new)

MISSING: Avax\Components\Application\Filesystem\Directories\ListDirectoryFiles  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:12  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:103  (new)

MISSING: Avax\Components\Application\Filesystem\Disks\Local\LocalDisk  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Capabilities/Disks/ResolveDisk.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Capabilities/Disks/ResolveDisk.php:24  (new)

MISSING: Avax\Components\Application\Filesystem\Disks\ResolveDisk  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Configuration/RegisterFilesystem.php:10  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Configuration/RegisterFilesystem.php:24  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Configuration/RegisterFilesystem.php:25  (new)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Configuration/RegisterFilesystem.php:32  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:14  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:29  (constructor-param)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:29  (new)

MISSING: Avax\Components\Application\Filesystem\Files\AppendToFile  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:15  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:48  (new)

MISSING: Avax\Components\Application\Filesystem\Files\CopyFile  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:16  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:53  (new)

MISSING: Avax\Components\Application\Filesystem\Files\DeleteFile  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:17  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:68  (new)

MISSING: Avax\Components\Application\Filesystem\Files\MoveFile  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:18  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:58  (new)

MISSING: Avax\Components\Application\Filesystem\Files\ReadFile  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:19  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:33  (new)

MISSING: Avax\Components\Application\Filesystem\Files\ReadFileLastModifiedAt  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:20  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:73  (new)

MISSING: Avax\Components\Application\Filesystem\Files\WriteFile  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:21  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:43  (new)

MISSING: Avax\Components\Application\Filesystem\Filesystem  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Configuration/RegisterFilesystem.php:11  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Configuration/RegisterFilesystem.php:36  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Configuration/RegisterFilesystem.php:37  (new)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Configuration/RegisterFilesystem.php:44  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:22  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Foundation/Implementation/LocalFilesystem.php:27  (implements)

MISSING: Avax\Components\Application\Filesystem\FilesystemInterface  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Configuration/RegisterFilesystem.php:12  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/Configuration/RegisterFilesystem.php:43  (class-const-fetch)

MISSING: Avax\Components\Application\SystemDesign\ArchitectureTests\System\PublicSurface\ArchitectureTest  [CRITICAL]
  - /home/shomsy/projects/avax/tests/SystemDesign/ArchitectureTests/ArchitectureTestTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/ArchitectureTests/ArchitectureTestTest.php:14  (new)
  - /home/shomsy/projects/avax/tests/SystemDesign/ArchitectureTests/ArchitectureTestTest.php:26  (new)

MISSING: Avax\Components\Application\SystemDesign\Availability\System\Flows\CalculateAvailability\CalculateAvailability  [CRITICAL]
  - /home/shomsy/projects/avax/tests/SystemDesign/Availability/AvailabilityModelTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Availability/AvailabilityModelTest.php:28  (new)

MISSING: Avax\Components\Application\SystemDesign\Availability\System\PublicSurface\AvailabilityModel  [CRITICAL]
  - /home/shomsy/projects/avax/tests/SystemDesign/Availability/AvailabilityModelTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Availability/AvailabilityModelTest.php:15  (new)
  - /home/shomsy/projects/avax/tests/SystemDesign/Availability/AvailabilityModelTest.php:27  (new)

MISSING: Avax\Components\Application\SystemDesign\Caching\System\Flows\AnalyzeCacheStrategy\AnalyzeCacheStrategy  [CRITICAL]
  - /home/shomsy/projects/avax/tests/SystemDesign/Caching/CacheStrategyTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Caching/CacheStrategyTest.php:15  (new)
  - /home/shomsy/projects/avax/tests/SystemDesign/Caching/CacheStrategyTest.php:26  (new)

MISSING: Avax\Components\Application\SystemDesign\Caching\System\PublicSurface\CacheStrategy  [MINOR]
  - /home/shomsy/projects/avax/tests/SystemDesign/Caching/CacheStrategyTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Caching/CacheStrategyTest.php:16  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/SystemDesign/Caching/CacheStrategyTest.php:27  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/SystemDesign/Caching/CacheStrategyTest.php:37  (static-call)

MISSING: Avax\Components\Application\SystemDesign\Capacity\System\Flows\EstimateTrafficLoad\EstimateTrafficLoad  [CRITICAL]
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/EstimateTrafficLoadTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/EstimateTrafficLoadTest.php:23  (new)
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/EstimateTrafficLoadTest.php:39  (new)

MISSING: Avax\Components\Application\SystemDesign\Capacity\System\Flows\ValidateCapacityModel\ValidateCapacityModel  [CRITICAL]
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/CapacityModelTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/CapacityModelTest.php:43  (new)
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/ValidateCapacityModelTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/ValidateCapacityModelTest.php:19  (new)
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/ValidateCapacityModelTest.php:30  (new)

MISSING: Avax\Components\Application\SystemDesign\Capacity\System\PublicSurface\CapacityBudget  [CRITICAL]
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/CapacityModelTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/CapacityModelTest.php:41  (new)
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/CapacityModelTest.php:52  (new)
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/ValidateCapacityModelTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/ValidateCapacityModelTest.php:17  (new)
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/ValidateCapacityModelTest.php:28  (new)

MISSING: Avax\Components\Application\SystemDesign\Capacity\System\PublicSurface\CapacityModel  [CRITICAL]
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/CapacityModelTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/CapacityModelTest.php:17  (new)
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/CapacityModelTest.php:40  (new)
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/ValidateCapacityModelTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/ValidateCapacityModelTest.php:16  (new)
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/ValidateCapacityModelTest.php:27  (new)
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/EstimateTrafficLoadTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/EstimateTrafficLoadTest.php:15  (new)
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/EstimateTrafficLoadTest.php:32  (new)

MISSING: Avax\Components\Application\SystemDesign\Capacity\System\PublicSurface\CapacityReport  [MINOR]
  - /home/shomsy/projects/avax/tests/SystemDesign/Capacity/CapacityModelTest.php:10  (use-statement)

MISSING: Avax\Components\Application\SystemDesign\Consistency\System\Flows\AnalyzeConsistency\AnalyzeConsistency  [CRITICAL]
  - /home/shomsy/projects/avax/tests/SystemDesign/Consistency/ConsistencyLevelTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Consistency/ConsistencyLevelTest.php:15  (new)
  - /home/shomsy/projects/avax/tests/SystemDesign/Consistency/ConsistencyLevelTest.php:25  (new)

MISSING: Avax\Components\Application\SystemDesign\Consistency\System\PublicSurface\ConsistencyLevel  [MINOR]
  - /home/shomsy/projects/avax/tests/SystemDesign/Consistency/ConsistencyLevelTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Consistency/ConsistencyLevelTest.php:16  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/SystemDesign/Consistency/ConsistencyLevelTest.php:26  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/SystemDesign/Consistency/ConsistencyLevelTest.php:35  (static-call)

MISSING: Avax\Components\Application\SystemDesign\Failure\System\Flows\AnalyzeFailureMode\AnalyzeFailureMode  [CRITICAL]
  - /home/shomsy/projects/avax/tests/SystemDesign/Failure/FailureModeTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Failure/FailureModeTest.php:15  (new)
  - /home/shomsy/projects/avax/tests/SystemDesign/Failure/FailureModeTest.php:26  (new)

MISSING: Avax\Components\Application\SystemDesign\Failure\System\PublicSurface\FailureMode  [MINOR]
  - /home/shomsy/projects/avax/tests/SystemDesign/Failure/FailureModeTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Failure/FailureModeTest.php:16  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/SystemDesign/Failure/FailureModeTest.php:27  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/SystemDesign/Failure/FailureModeTest.php:36  (static-call)

MISSING: Avax\Components\Application\SystemDesign\LatencyBudget\System\Flows\AllocateLatency\AllocateLatency  [CRITICAL]
  - /home/shomsy/projects/avax/tests/SystemDesign/LatencyBudget/LatencyBudgetTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/LatencyBudget/LatencyBudgetTest.php:27  (new)

MISSING: Avax\Components\Application\SystemDesign\LatencyBudget\System\PublicSurface\LatencyBudget  [CRITICAL]
  - /home/shomsy/projects/avax/tests/SystemDesign/LatencyBudget/LatencyBudgetTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/LatencyBudget/LatencyBudgetTest.php:15  (new)
  - /home/shomsy/projects/avax/tests/SystemDesign/LatencyBudget/LatencyBudgetTest.php:28  (new)

MISSING: Avax\Components\Application\SystemDesign\LoadModel\System\Flows\EstimateLoad\EstimateLoad  [CRITICAL]
  - /home/shomsy/projects/avax/tests/SystemDesign/LoadModel/LoadModelTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/LoadModel/LoadModelTest.php:34  (new)

MISSING: Avax\Components\Application\SystemDesign\LoadModel\System\PublicSurface\LoadModel  [CRITICAL]
  - /home/shomsy/projects/avax/tests/SystemDesign/LoadModel/LoadModelTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/LoadModel/LoadModelTest.php:15  (new)
  - /home/shomsy/projects/avax/tests/SystemDesign/LoadModel/LoadModelTest.php:28  (new)

MISSING: Avax\Components\Application\SystemDesign\Messaging\System\Flows\AnalyzeMessaging\AnalyzeMessaging  [CRITICAL]
  - /home/shomsy/projects/avax/tests/SystemDesign/Messaging/MessagingPatternTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Messaging/MessagingPatternTest.php:15  (new)
  - /home/shomsy/projects/avax/tests/SystemDesign/Messaging/MessagingPatternTest.php:25  (new)

MISSING: Avax\Components\Application\SystemDesign\Messaging\System\PublicSurface\MessagingPattern  [MINOR]
  - /home/shomsy/projects/avax/tests/SystemDesign/Messaging/MessagingPatternTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Messaging/MessagingPatternTest.php:16  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/SystemDesign/Messaging/MessagingPatternTest.php:26  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/SystemDesign/Messaging/MessagingPatternTest.php:35  (static-call)

MISSING: Avax\Components\Application\SystemDesign\Partitioning\System\Flows\DesignPartitioning\DesignPartitioning  [CRITICAL]
  - /home/shomsy/projects/avax/tests/SystemDesign/Partitioning/PartitioningStrategyTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Partitioning/PartitioningStrategyTest.php:20  (new)

MISSING: Avax\Components\Application\SystemDesign\Partitioning\System\PublicSurface\PartitioningStrategy  [MINOR]
  - /home/shomsy/projects/avax/tests/SystemDesign/Partitioning/PartitioningStrategyTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Partitioning/PartitioningStrategyTest.php:15  (static-call)
  - /home/shomsy/projects/avax/tests/SystemDesign/Partitioning/PartitioningStrategyTest.php:21  (class-const-fetch)

MISSING: Avax\Components\Application\SystemDesign\Projections\System\Flows\DesignProjection\DesignProjection  [CRITICAL]
  - /home/shomsy/projects/avax/tests/SystemDesign/Projections/ProjectionTypeTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Projections/ProjectionTypeTest.php:20  (new)

MISSING: Avax\Components\Application\SystemDesign\Projections\System\PublicSurface\ProjectionType  [MINOR]
  - /home/shomsy/projects/avax/tests/SystemDesign/Projections/ProjectionTypeTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Projections/ProjectionTypeTest.php:15  (static-call)
  - /home/shomsy/projects/avax/tests/SystemDesign/Projections/ProjectionTypeTest.php:21  (class-const-fetch)

MISSING: Avax\Components\Application\SystemDesign\Replication\System\Flows\AnalyzeReplication\AnalyzeReplication  [CRITICAL]
  - /home/shomsy/projects/avax/tests/SystemDesign/Replication/ReplicationStrategyTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Replication/ReplicationStrategyTest.php:20  (new)

MISSING: Avax\Components\Application\SystemDesign\Replication\System\PublicSurface\ReplicationStrategy  [MINOR]
  - /home/shomsy/projects/avax/tests/SystemDesign/Replication/ReplicationStrategyTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Replication/ReplicationStrategyTest.php:15  (static-call)
  - /home/shomsy/projects/avax/tests/SystemDesign/Replication/ReplicationStrategyTest.php:21  (class-const-fetch)

MISSING: Avax\Components\Application\SystemDesign\Sharding\System\Flows\AnalyzeSharding\AnalyzeSharding  [CRITICAL]
  - /home/shomsy/projects/avax/tests/SystemDesign/Sharding/ShardingKeyTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Sharding/ShardingKeyTest.php:27  (new)

MISSING: Avax\Components\Application\SystemDesign\Sharding\System\PublicSurface\ShardingKey  [CRITICAL]
  - /home/shomsy/projects/avax/tests/SystemDesign/Sharding/ShardingKeyTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Sharding/ShardingKeyTest.php:15  (new)
  - /home/shomsy/projects/avax/tests/SystemDesign/Sharding/ShardingKeyTest.php:26  (new)

MISSING: Avax\Components\Application\SystemDesign\Simulation\System\PublicSurface\SimulationConfig  [CRITICAL]
  - /home/shomsy/projects/avax/tests/SystemDesign/Simulation/SimulationConfigTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/SystemDesign/Simulation/SimulationConfigTest.php:14  (new)
  - /home/shomsy/projects/avax/tests/SystemDesign/Simulation/SimulationConfigTest.php:23  (new)

MISSING: Avax\Components\Application\Text\MatchResult  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:9  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:167  (return-type)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:5  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:140  (return-type)

MISSING: Avax\Components\Application\Text\Pattern  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Validation/System/Capabilities/Standard/Email/ValidateEmail.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Validation/System/Capabilities/Standard/Email/ValidateEmail.php:32  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:10  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:147  (return-type)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:149  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:159  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:169  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:179  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:189  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:199  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:6  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:116  (return-type)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:118  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:130  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:142  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:155  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:168  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:180  (static-call)

MISSING: Avax\Components\Application\Text\Text  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:11  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:17  (return-type)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:19  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:27  (return-type)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:29  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:39  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:49  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:59  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:69  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:79  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:89  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:99  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:109  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:119  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:129  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/System/PublicSurface/shortcuts.php:139  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:12  (return-type)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:14  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:20  (return-type)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:22  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:30  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:38  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:46  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:54  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:62  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:70  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:78  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:86  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:94  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:102  (static-call)
  - /home/shomsy/projects/avax/components/Application/Text/functions.php:110  (static-call)

MISSING: Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes\DefaultValue  [MINOR]
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataTransfer/DataTransfer.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataTransfer/DataTransfer.php:111  (class-const-fetch)

MISSING: Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes\Optional  [MINOR]
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataTransfer/DataTransfer.php:12  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataTransfer/DataTransfer.php:88  (class-const-fetch)

MISSING: Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes\PasswordComplexity  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Validation/System/Capabilities/Execution/ValidateDto.php:10  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Validation/System/Capabilities/Execution/ValidateDto.php:42  (instanceof)

MISSING: Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes\PasswordComplexityRule  [CRITICAL]
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataTransfer/DataTransfer.php:13  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataTransfer/DataTransfer.php:162  (new)

MISSING: Avax\Components\Auth\Interface\HTTP\Middleware\AuthenticationMiddleware  [MINOR]
  - /home/shomsy/projects/avax/examples/minimal-http-app/config/middleware.php:12  (use-statement)
  - /home/shomsy/projects/avax/examples/minimal-http-app/config/middleware.php:34  (class-const-fetch)

MISSING: Avax\Components\Components\Application\Cache\Unit\Cache\Providers\CacheNotConfigured  [MINOR]
  - /home/shomsy/projects/avax/tests/Unit/Components/Application/Cache/Unit/Cache/Providers/CacheServiceProviderTest.php:31  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Unit/Components/Application/Cache/Unit/Cache/Providers/CacheServiceProviderTest.php:75  (class-const-fetch)

MISSING: Avax\Components\Container\BindingBuilderInterface  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/ViewServiceProviderTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/ViewServiceProviderTest.php:22  (class-const-fetch)

MISSING: Avax\Components\Container\ContainerInterface  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/ViewServiceProviderTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/ViewServiceProviderTest.php:33  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Contracts/ServiceProviderContractTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Contracts/ServiceProviderContractTest.php:20  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:38  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:69  (class-const-fetch)

MISSING: Avax\Components\Container\Core\AppFactory  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Flows/RegisterBindings/RegisterBindingsTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Flows/RegisterBindings/RegisterBindingsTest.php:74  (static-call)

MISSING: Avax\Components\Container\DI\Capabilities\Diagnostics\Errors\ContainerException  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineWiringTest.php:16  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineWiringTest.php:31  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStageHandlersTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStageHandlersTest.php:52  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:45  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:53  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:73  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:95  (class-const-fetch)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Definitions\Store\DefinitionStore  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineWiringTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineWiringTest.php:47  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Injection\InjectDependencies  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:39  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:70  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:52  (return-type)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:56  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Injection\Methods\MethodInjector  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:42  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:73  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:59  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Injection\Parameters\ResolveMethodParameters  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:43  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:74  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:60  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Injection\Properties\PropertyInjector  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:41  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:72  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:58  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Invocation\InvokeAction  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:73  (return-type)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:75  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Observability\Metrics\CollectMetrics  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineWiringTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineWiringTest.php:49  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:13  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:37  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:87  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:106  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Observability\Timeline\ResolutionTimeline  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:14  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:36  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:86  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:105  (class-const-fetch)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Observability\Trace\ResolutionTrace  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Errors/ResolutionExceptionWithTraceTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Errors/ResolutionExceptionWithTraceTest.php:16  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Policies\ContainerPolicy  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:15  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:38  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:88  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:107  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Prototypes\Analyze\PrototypeAnalyzer  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Prototypes/Factory/ServicePrototypeFactoryTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Prototypes/Factory/ServicePrototypeFactoryTest.php:75  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:16  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:69  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Prototypes\Analyze\ReflectionTypeAnalyzer  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Prototypes/Factory/ServicePrototypeFactoryTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Prototypes/Factory/ServicePrototypeFactoryTest.php:75  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:17  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:69  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Prototypes\Cache\FilePrototypeCache  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:18  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:68  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Prototypes\Cache\PrototypeCache  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Prototypes/Factory/ServicePrototypeFactoryTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Prototypes/Factory/ServicePrototypeFactoryTest.php:19  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Prototypes/Factory/ServicePrototypeFactoryTest.php:72  (class-const-fetch)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Prototypes\Contracts\ServicePrototypeFactoryInterface  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineWiringTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineWiringTest.php:40  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:13  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:35  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:66  (class-const-fetch)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Prototypes\Factory\ServicePrototypeBuilder  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Prototypes/Factory/ServicePrototypeBuilderTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Prototypes/Factory/ServicePrototypeBuilderTest.php:20  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Prototypes/Factory/ServicePrototypeBuilderTest.php:25  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Prototypes\Factory\ServicePrototypeFactory  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Prototypes/Factory/ServicePrototypeFactoryTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Prototypes/Factory/ServicePrototypeFactoryTest.php:21  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Prototypes/Factory/ServicePrototypeFactoryTest.php:73  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:19  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:65  (return-type)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:67  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Prototypes\Model\MethodPrototype  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/var/cache/Avax_Logging_ErrorHandler.php:5  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/var/cache/Avax_Logging_ErrorHandler.php:12  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Container/var/cache/Avax_Logging_ErrorLogger.php:5  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/var/cache/Avax_Logging_ErrorLogger.php:12  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Prototypes/Factory/ServicePrototypeBuilderTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Prototypes/Factory/ServicePrototypeBuilderTest.php:27  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Prototypes/Factory/ServicePrototypeBuilderTest.php:32  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:14  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:56  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Prototypes\Model\ParameterPrototype  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/var/cache/Avax_Logging_ErrorHandler.php:6  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/var/cache/Avax_Logging_ErrorHandler.php:15  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Container/var/cache/Avax_Logging_ErrorLogger.php:6  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/var/cache/Avax_Logging_ErrorLogger.php:15  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Prototypes/Factory/ServicePrototypeBuilderTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Prototypes/Factory/ServicePrototypeBuilderTest.php:29  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:15  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:58  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Prototypes\Model\PropertyPrototype  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Prototypes/Factory/ServicePrototypeBuilderTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Prototypes/Factory/ServicePrototypeBuilderTest.php:31  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:16  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:31  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Prototypes\Model\ServicePrototype  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/var/cache/Avax_Logging_ErrorHandler.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/var/cache/Avax_Logging_ErrorHandler.php:10  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Container/var/cache/Avax_Logging_ErrorLogger.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/var/cache/Avax_Logging_ErrorLogger.php:10  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Prototypes/Factory/ServicePrototypeFactoryTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Prototypes/Factory/ServicePrototypeFactoryTest.php:34  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Prototypes/Factory/ServicePrototypeFactoryTest.php:41  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:17  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:29  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:60  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Providers\Contracts\RegisterDependency  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Contracts/ServiceProviderContractTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Contracts/ServiceProviderContractTest.php:16  (class-const-fetch)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Providers\Runtime\Http\HttpApplication  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Flows/RegisterBindings/RegisterBindingsTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Flows/RegisterBindings/RegisterBindingsTest.php:23  (property-type)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Providers\Runtime\Http\MiddlewareBaseRegisterDependency  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Flows/RegisterBindings/RegisterBindingsTest.php:13  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Flows/RegisterBindings/RegisterBindingsTest.php:75  (class-const-fetch)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Providers\Runtime\Http\RouterBaseRegisterDependency  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Flows/RegisterBindings/RegisterBindingsTest.php:14  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Flows/RegisterBindings/RegisterBindingsTest.php:75  (class-const-fetch)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Providers\Runtime\Http\ViewBaseRegisterDependency  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/ViewServiceProviderTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/ViewServiceProviderTest.php:48  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Resolution\Contracts\ContainerRuntimeInterface  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineWiringTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineWiringTest.php:27  (class-const-fetch)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Resolution\Engine\DependencyResolver  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineWiringTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineWiringTest.php:38  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:18  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:43  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:74  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:20  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:54  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:75  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Resolution\Engine\EngineInterface  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Steps/ResolveInstanceStepTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Steps/ResolveInstanceStepTest.php:21  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Steps/ResolveInstanceStepTest.php:38  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:21  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:31  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:81  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:100  (class-const-fetch)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Resolution\Engine\Instantiator  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineWiringTest.php:13  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineWiringTest.php:39  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Resolution\Engine\ResolutionEngine  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineWiringTest.php:14  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineWiringTest.php:22  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineWiringTest.php:44  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Resolution\Errors\ResolutionException  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:19  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Injection/InjectDependenciesTest.php:48  (class-const-fetch)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Resolution\Errors\ResolutionExceptionWithTrace  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Errors/ResolutionExceptionWithTraceTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Errors/ResolutionExceptionWithTraceTest.php:20  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Resolution\Kernel\KernelContext  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/KernelContextTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/KernelContextTest.php:16  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/KernelContextTest.php:30  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/KernelContextTest.php:41  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/KernelContextTest.php:50  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/KernelContextTest.php:59  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStageHandlersTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStageHandlersTest.php:27  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Steps/ResolveInstanceStepTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Steps/ResolveInstanceStepTest.php:24  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Steps/ResolveInstanceStepTest.php:28  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Steps/ResolveInstanceStepTest.php:44  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:20  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:23  (param-type)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:30  (param-type)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:62  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:65  (param-type)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Resolution\Pipeline\Contracts\KernelStep  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:22  (implements)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:29  (implements)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:64  (implements)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:81  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:82  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:93  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:103  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:104  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:105  (class-const-fetch)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Resolution\Pipeline\ResolutionPipeline  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:36  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:48  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:56  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:71  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:84  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:93  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/ResolutionPipelineTest.php:107  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Resolution\Pipeline\Steps\ResolveInstanceStep  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Steps/ResolveInstanceStepTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Steps/ResolveInstanceStepTest.php:27  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Steps/ResolveInstanceStepTest.php:43  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Resolution\Pipeline\Strategies\ResolutionStageHandlers  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStageHandlersTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStageHandlersTest.php:17  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStageHandlersTest.php:34  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStageHandlersTest.php:48  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Resolution\Pipeline\Strategies\ResolutionState  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Errors/ResolutionExceptionWithTraceTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Errors/ResolutionExceptionWithTraceTest.php:17  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Errors/ResolutionExceptionWithTraceTest.php:18  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStageHandlersTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStageHandlersTest.php:18  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStageHandlersTest.php:19  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStageHandlersTest.php:23  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStageHandlersTest.php:28  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStageHandlersTest.php:35  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStageHandlersTest.php:36  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStageHandlersTest.php:40  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStageHandlersTest.php:41  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStageHandlersTest.php:43  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStageHandlersTest.php:49  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Pipeline/Strategies/ResolutionStageHandlersTest.php:53  (class-const-fetch)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Scopes\ScopeManager  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Scopes/ScopeManagerTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Scopes/ScopeManagerTest.php:18  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Scopes/ScopeManagerTest.php:30  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Scopes/ScopeManagerTest.php:48  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Scopes/ScopeManagerTest.php:57  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:22  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:34  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:84  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:103  (new)

MISSING: Avax\Components\Container\DependencyInjection\Capability\Scopes\ScopeRegistry  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineWiringTest.php:15  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Engine/ResolutionEngineWiringTest.php:48  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Scopes/ScopeManagerTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Scopes/ScopeManagerTest.php:17  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Scopes/ScopeManagerTest.php:29  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Scopes/ScopeManagerTest.php:48  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Scopes/ScopeManagerTest.php:56  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:23  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:34  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:84  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:103  (new)

MISSING: Avax\Components\Container\DependencyInjection\Configuration\KernelConfigFactory  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:24  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:47  (return-type)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Configuration/KernelConfigFactoryTest.php:49  (new)

MISSING: Avax\Components\Container\DependencyInjection\Configuration\Settings  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/ViewServiceProviderTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/ViewServiceProviderTest.php:46  (new)

MISSING: Avax\Components\DataFoundation\Arrhae  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Arrhae/ArrhaeCharacterizationTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Arrhae/ArrhaeCharacterizationTest.php:28  (return-type)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Arrhae/ArrhaeCharacterizationTest.php:30  (new)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Arrhae/ArrhaeCharacterizationTest.php:506  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Arrhae/ArrhaeCharacterizationTest.php:513  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Arrhae/ArrhaeCharacterizationTest.php:520  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Arrhae/ArrhaeCharacterizationTest.php:527  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Arrhae/ArrhaeCharacterizationTest.php:534  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Arrhae/ArrhaeCharacterizationTest.php:535  (static-call)

MISSING: Avax\Components\DataFoundation\Collection  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Config/System/Capabilities/Configuration/AppConfigurator.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Config/System/Capabilities/Configuration/AppConfigurator.php:26  (property-type)
  - /home/shomsy/projects/avax/components/Application/Config/System/Capabilities/Configuration/AppConfigurator.php:63  (return-type)
  - /home/shomsy/projects/avax/components/Application/Config/System/Capabilities/Configuration/AppConfigurator.php:81  (return-type)
  - /home/shomsy/projects/avax/components/Application/Config/System/Capabilities/Configuration/AppConfigurator.php:137  (return-type)
  - /home/shomsy/projects/avax/components/Application/Config/System/Capabilities/Configuration/AppConfigurator.php:164  (return-type)
  - /home/shomsy/projects/avax/components/Application/Config/Configurator/AppConfigurator.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Config/Configurator/AppConfigurator.php:25  (property-type)
  - /home/shomsy/projects/avax/components/Application/Config/Configurator/AppConfigurator.php:65  (return-type)
  - /home/shomsy/projects/avax/components/Application/Config/Configurator/AppConfigurator.php:83  (return-type)
  - /home/shomsy/projects/avax/components/Application/Config/Configurator/AppConfigurator.php:138  (return-type)
  - /home/shomsy/projects/avax/components/Application/Config/Configurator/AppConfigurator.php:163  (return-type)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Collection/CollectionCharacterizationTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Collection/CollectionCharacterizationTest.php:25  (return-type)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Collection/CollectionCharacterizationTest.php:27  (new)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Collection/CollectionCharacterizationTest.php:301  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Collection/CollectionCharacterizationTest.php:308  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Collection/CollectionCharacterizationTest.php:316  (static-call)

MISSING: Avax\Components\DataHandling\DataTransfer\Capabilities\Attributes\Hidden  [MINOR]
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferPublicApiTest.php:7  (use-statement)

MISSING: Avax\Components\DataHandling\DataTransfer\Capabilities\Attributes\ListOf  [MINOR]
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferPublicApiTest.php:8  (use-statement)

MISSING: Avax\Components\DataHandling\DataTransfer\Capabilities\Attributes\MapFrom  [MINOR]
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferPublicApiTest.php:9  (use-statement)

MISSING: Avax\Components\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferFailure  [MINOR]
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferPublicApiTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferPublicApiTest.php:122  (class-const-fetch)

MISSING: Avax\Components\DataHandling\DataTransfer\Configuration\DataTransferConfig  [MINOR]
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferPublicApiTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferPublicApiTest.php:85  (static-call)

MISSING: Avax\Components\DataHandling\DataTransfer\Configuration\UnknownFieldPolicy  [MINOR]
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferPublicApiTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferPublicApiTest.php:85  (class-const-fetch)

MISSING: Avax\Components\DataHandling\DataTransfer\DataTransfer  [MINOR]
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferPublicApiTest.php:13  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferPublicApiTest.php:33  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferPublicApiTest.php:55  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferPublicApiTest.php:72  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferPublicApiTest.php:88  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferPublicApiTest.php:107  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferPublicApiTest.php:125  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferPublicApiTest.php:131  (static-call)

MISSING: Avax\Components\DataLayer\AccessPersistentData  [CRITICAL]
  - /home/shomsy/projects/avax/components/DataLayer/DataLayer.php:11  (property-type)
  - /home/shomsy/projects/avax/components/DataLayer/DataLayer.php:15  (constructor-param)
  - /home/shomsy/projects/avax/components/DataLayer/DataLayer.php:25  (new)
  - /home/shomsy/projects/avax/components/DataLayer/DataLayer.php:30  (return-type)

MISSING: Avax\Components\DataLayer\CommitDataChanges  [CRITICAL]
  - /home/shomsy/projects/avax/components/DataLayer/DataLayer.php:12  (property-type)
  - /home/shomsy/projects/avax/components/DataLayer/DataLayer.php:15  (constructor-param)
  - /home/shomsy/projects/avax/components/DataLayer/DataLayer.php:26  (new)
  - /home/shomsy/projects/avax/components/DataLayer/DataLayer.php:35  (return-type)

MISSING: Avax\Components\DataLayer\DataLayerConfig  [CRITICAL]
  - /home/shomsy/projects/avax/components/DataLayer/DataLayer.php:13  (property-type)
  - /home/shomsy/projects/avax/components/DataLayer/DataLayer.php:15  (constructor-param)
  - /home/shomsy/projects/avax/components/DataLayer/DataLayer.php:40  (return-type)

MISSING: Avax\Components\DataLayer\RegisterDataLayerRuntime  [CRITICAL]
  - /home/shomsy/projects/avax/components/DataLayer/DataLayer.php:24  (new)

MISSING: Avax\Components\DataStack\Data\Collections\Sequence\Sequence  [CRITICAL]
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Flows/LazySequence/LazySequence.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Flows/LazySequence/LazySequence.php:64  (return-type)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Flows/LazySequence/LazySequence.php:66  (new)

MISSING: Avax\Components\DataStack\Data\Exceptions\InvalidFlowException  [MINOR]
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Flows/Window/Window.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Flows/Window/Window.php:30  (static-call)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Flows/Window/Window.php:34  (static-call)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Flows/Batch/Batch.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Flows/Batch/Batch.php:30  (static-call)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Flows/Pipeline/Pipeline.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Flows/Pipeline/Pipeline.php:32  (static-call)

MISSING: Avax\Components\DataStack\Data\Exceptions\InvalidValueException  [MINOR]
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/Collections/Internal/Composites/RecordField.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/Collections/Internal/Composites/RecordField.php:19  (static-call)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/Collections/Internal/Values/Result/Success.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/Collections/Internal/Values/Result/Success.php:34  (static-call)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/Collections/Internal/Values/Result/Failure.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/Collections/Internal/Values/Result/Failure.php:28  (static-call)

MISSING: Avax\Components\DataStack\Data\Internal\Iteration\NormalizedIterable  [MINOR]
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Flows/Window/Window.php:9  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Flows/Window/Window.php:37  (static-call)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Flows/Batch/Batch.php:9  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Flows/Batch/Batch.php:35  (static-call)

MISSING: Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Capabilities\ErrorReporting\DataTransferFailure  [CRITICAL]
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataTransfer/DataTransferResult.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataTransfer/DataTransferResult.php:13  (constructor-param)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataTransfer/DataTransferResult.php:21  (param-type)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataTransfer/DataTransferResult.php:33  (instanceof)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataTransfer/DataTransferResult.php:41  (return-type)

MISSING: Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Compatibility\LegacyAbstractDTO  [MINOR]
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataShape/ReadConstructorDataFields.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataShape/ReadConstructorDataFields.php:35  (class-const-fetch)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataShape/ReadConstructorDataFields.php:36  (class-const-fetch)

MISSING: Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\DataTransferConfig  [MINOR]
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataTransfer/DataTransfer.php:31  (property-type)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataTransfer/DataTransfer.php:33  (param-type)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataTransfer/DataTransfer.php:72  (static-call)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataTransfer/DataTransfer.php:179  (return-type)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataTransfer/DataTransfer.php:181  (static-call)

MISSING: Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\DataTransferFailure  [CRITICAL]
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataTransfer/DataTransfer.php:49  (catch)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataTransfer/DataTransfer.php:53  (new)
  - /home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataTransfer/DataTransfer.php:139  (new)

MISSING: Avax\Components\DataStack\Data\System\Capabilities\ObjectHandling\DTO\AbstractDTO  [MINOR]
  - /home/shomsy/projects/avax/components/compat.php:26  (use-statement)
  - /home/shomsy/projects/avax/components/compat.php:46  (class-const-fetch)

MISSING: Avax\Components\DataStack\Database\AccessPersistentData\PersistentDataFailure  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/AccessPersistentData/AccessPersistentDataTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/AccessPersistentData/AccessPersistentDataTest.php:19  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/AccessPersistentData/AccessPersistentDataTest.php:29  (class-const-fetch)

MISSING: Avax\Components\DataStack\Database\AccessPersistentData\PersistentDataRequest  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/AccessPersistentData/AccessPersistentDataTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/AccessPersistentData/AccessPersistentDataTest.php:22  (new)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/AccessPersistentData/AccessPersistentDataTest.php:32  (new)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/AccessPersistentData/AccessPersistentDataTest.php:43  (new)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/AccessPersistentData/AccessPersistentDataTest.php:69  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/AccessPersistentData/AccessPersistentDataTest.php:73  (param-type)

MISSING: Avax\Components\DataStack\Database\AccessPersistentData\PersistentDataResult  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/AccessPersistentData/AccessPersistentDataTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/AccessPersistentData/AccessPersistentDataTest.php:73  (return-type)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/AccessPersistentData/AccessPersistentDataTest.php:77  (new)

MISSING: Avax\Components\DataStack\Database\Collections\DataList\DataList  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Collections/CollectionFamilyTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Collections/CollectionFamilyTest.php:18  (new)

MISSING: Avax\Components\DataStack\Database\Collections\Map\Map  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Collections/CollectionFamilyTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Collections/CollectionFamilyTest.php:32  (new)

MISSING: Avax\Components\DataStack\Database\Collections\MultiMap\MultiMap  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Collections/CollectionFamilyTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Collections/CollectionFamilyTest.php:40  (new)

MISSING: Avax\Components\DataStack\Database\Collections\Sequence\Sequence  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Collections/CollectionFamilyTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Collections/CollectionFamilyTest.php:47  (new)

MISSING: Avax\Components\DataStack\Database\Collections\Set\Set  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Collections/CollectionFamilyTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Collections/CollectionFamilyTest.php:25  (new)

MISSING: Avax\Components\DataStack\Database\CommitDataChanges\CommitDataChanges  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/CommitDataChanges/CommitDataChangesTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/CommitDataChanges/CommitDataChangesTest.php:17  (new)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/CommitDataChanges/CommitDataChangesTest.php:27  (new)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/CommitDataChanges/CommitDataChangesTest.php:39  (new)

MISSING: Avax\Components\DataStack\Database\CommitDataChanges\DataTransactionFailure  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/CommitDataChanges/CommitDataChangesTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/CommitDataChanges/CommitDataChangesTest.php:29  (class-const-fetch)

MISSING: Avax\Components\DataStack\Database\CommitDataChanges\DataTransactionPolicy  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/CommitDataChanges/CommitDataChangesTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/CommitDataChanges/CommitDataChangesTest.php:33  (new)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/CommitDataChanges/CommitDataChangesTest.php:52  (new)

MISSING: Avax\Components\DataStack\Database\Composites\MapEntry\MapEntry  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Composites/CompositeFamilyTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Composites/CompositeFamilyTest.php:53  (new)

MISSING: Avax\Components\DataStack\Database\Composites\Pair\Pair  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Composites/CompositeFamilyTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Composites/CompositeFamilyTest.php:20  (new)

MISSING: Avax\Components\DataStack\Database\Composites\Record\Record  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Composites/CompositeFamilyTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Composites/CompositeFamilyTest.php:40  (static-call)

MISSING: Avax\Components\DataStack\Database\Composites\Record\RecordField  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Composites/CompositeFamilyTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Composites/CompositeFamilyTest.php:41  (new)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Composites/CompositeFamilyTest.php:42  (new)

MISSING: Avax\Components\DataStack\Database\Composites\Tuple\Tuple2  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Composites/CompositeFamilyTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Composites/CompositeFamilyTest.php:29  (new)

MISSING: Avax\Components\DataStack\Database\Composites\Tuple\Tuple3  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Composites/CompositeFamilyTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Composites/CompositeFamilyTest.php:30  (new)

MISSING: Avax\Components\DataStack\Database\Composites\Tuple\Tuple4  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Composites/CompositeFamilyTest.php:13  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Composites/CompositeFamilyTest.php:31  (new)

MISSING: Avax\Components\DataStack\Database\ConfigureDataLayer\DataLayerConfig  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/Configuration/DataLayerConfigurationTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/Configuration/DataLayerConfigurationTest.php:21  (new)

MISSING: Avax\Components\DataStack\Database\ConfigureDataLayer\DataLayerConfigurationFailure  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/Configuration/DataLayerConfigurationTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/Configuration/DataLayerConfigurationTest.php:19  (class-const-fetch)

MISSING: Avax\Components\DataStack\Database\ConfigureDataLayer\RegisterDataLayerRuntime  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/Configuration/DataLayerConfigurationTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/Configuration/DataLayerConfigurationTest.php:27  (new)

MISSING: Avax\Components\DataStack\Database\ConfigureDataLayer\ResolveDataLayerRuntime  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/Configuration/DataLayerConfigurationTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/Configuration/DataLayerConfigurationTest.php:21  (new)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/Configuration/DataLayerConfigurationTest.php:29  (new)

MISSING: Avax\Components\DataStack\Database\DataLayer  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/AccessPersistentData/AccessPersistentDataTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/AccessPersistentData/AccessPersistentDataTest.php:17  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/AccessPersistentData/AccessPersistentDataTest.php:27  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/AccessPersistentData/AccessPersistentDataTest.php:41  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/AccessPersistentData/AccessPersistentDataTest.php:55  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/Configuration/DataLayerConfigurationTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataLayer/Configuration/DataLayerConfigurationTest.php:38  (static-call)

MISSING: Avax\Components\DataStack\Database\Database  [MINOR]
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:9  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:41  (class-const-fetch)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:43  (class-const-fetch)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:45  (class-const-fetch)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:47  (class-const-fetch)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:49  (class-const-fetch)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:51  (class-const-fetch)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:53  (class-const-fetch)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:55  (class-const-fetch)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:76  (return-type)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:78  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Database/Core/KernelTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Database/Core/KernelTest.php:28  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Database/Core/KernelTest.php:39  (class-const-fetch)

MISSING: Avax\Components\DataStack\Database\EntityManager  [MINOR]
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:10  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:49  (class-const-fetch)

MISSING: Avax\Components\DataStack\Database\Flows\Batch\Batch  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Flows/FlowFamilyTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Flows/FlowFamilyTest.php:35  (static-call)

MISSING: Avax\Components\DataStack\Database\Flows\LazySequence\LazySequence  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Flows/FlowFamilyTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Flows/FlowFamilyTest.php:26  (static-call)

MISSING: Avax\Components\DataStack\Database\Flows\Pipeline\Pipeline  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Flows/FlowFamilyTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Flows/FlowFamilyTest.php:17  (new)

MISSING: Avax\Components\DataStack\Database\Flows\Window\Window  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Flows/FlowFamilyTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Flows/FlowFamilyTest.php:42  (static-call)

MISSING: Avax\Components\DataStack\Database\Interop\Arrays\FromArray  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Interop/InteropFamilyTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Interop/InteropFamilyTest.php:22  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Interop/InteropFamilyTest.php:23  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Interop/InteropFamilyTest.php:24  (static-call)

MISSING: Avax\Components\DataStack\Database\Interop\Arrays\ToArray  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Interop/InteropFamilyTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Interop/InteropFamilyTest.php:34  (static-call)

MISSING: Avax\Components\DataStack\Database\Interop\Generators\FromGenerator  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Interop/InteropFamilyTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Interop/InteropFamilyTest.php:44  (static-call)

MISSING: Avax\Components\DataStack\Database\Interop\Iterables\FromIterable  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Interop/InteropFamilyTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Interop/InteropFamilyTest.php:34  (static-call)

MISSING: Avax\Components\DataStack\Database\Interop\Iterables\ToIterable  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Interop/InteropFamilyTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Interop/InteropFamilyTest.php:33  (static-call)

MISSING: Avax\Components\DataStack\Database\Interop\Json\FromJson  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Interop/InteropFamilyTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Interop/InteropFamilyTest.php:60  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Interop/InteropFamilyTest.php:61  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Interop/InteropFamilyTest.php:62  (static-call)

MISSING: Avax\Components\DataStack\Database\Interop\Json\ToJson  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Interop/InteropFamilyTest.php:13  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Interop/InteropFamilyTest.php:58  (static-call)

MISSING: Avax\Components\DataStack\Database\Interop\Xml\FromXml  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Interop/InteropFamilyTest.php:14  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Interop/InteropFamilyTest.php:68  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Interop/InteropFamilyTest.php:72  (static-call)

MISSING: Avax\Components\DataStack\Database\Interop\Xml\ToXml  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Interop/InteropFamilyTest.php:15  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Interop/InteropFamilyTest.php:67  (static-call)

MISSING: Avax\Components\DataStack\Database\Migrations  [MINOR]
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:11  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:47  (class-const-fetch)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:62  (class-const-fetch)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:64  (class-const-fetch)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:66  (class-const-fetch)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:68  (class-const-fetch)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:70  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Database/Core/KernelTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Database/Core/KernelTest.php:44  (class-const-fetch)

MISSING: Avax\Components\DataStack\Database\Persistence  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Database/Core/KernelTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Database/Core/KernelTest.php:42  (class-const-fetch)

MISSING: Avax\Components\DataStack\Database\Query  [MINOR]
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:12  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:45  (class-const-fetch)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:57  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Database/Core/KernelTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Database/Core/KernelTest.php:41  (class-const-fetch)

MISSING: Avax\Components\DataStack\Database\Schema  [MINOR]
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:13  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:51  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Database/Core/KernelTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Database/Core/KernelTest.php:43  (class-const-fetch)

MISSING: Avax\Components\DataStack\Database\Schema\Blueprint  [CRITICAL]
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Flows/BuildDatabaseSchema/AlterTable.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Flows/BuildDatabaseSchema/AlterTable.php:11  (return-type)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Flows/BuildDatabaseSchema/AlterTable.php:13  (new)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Flows/BuildDatabaseSchema/CreateTable.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Flows/BuildDatabaseSchema/CreateTable.php:11  (return-type)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Flows/BuildDatabaseSchema/CreateTable.php:13  (new)

MISSING: Avax\Components\DataStack\Database\Structures\Deque\Deque  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Structures/StructureFamilyTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Structures/StructureFamilyTest.php:35  (new)

MISSING: Avax\Components\DataStack\Database\Structures\PriorityQueue\PriorityQueue  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Structures/StructureFamilyTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Structures/StructureFamilyTest.php:42  (new)

MISSING: Avax\Components\DataStack\Database\Structures\Queue\Queue  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Structures/StructureFamilyTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Structures/StructureFamilyTest.php:27  (new)

MISSING: Avax\Components\DataStack\Database\Structures\RingBuffer\RingBuffer  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Structures/StructureFamilyTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Structures/StructureFamilyTest.php:52  (new)

MISSING: Avax\Components\DataStack\Database\Structures\Stack\Stack  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Structures/StructureFamilyTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Structures/StructureFamilyTest.php:19  (new)

MISSING: Avax\Components\DataStack\Database\Structures\Tree\Tree  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Structures/StructureFamilyTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Structures/StructureFamilyTest.php:59  (new)

MISSING: Avax\Components\DataStack\Database\System\Capabilities\Connections\DatabaseConnection  [CRITICAL]
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Flows/RunDatabaseTransaction/RunDatabaseTransaction.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Flows/RunDatabaseTransaction/RunDatabaseTransaction.php:11  (param-type)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Flows/ConnectToDatabase/ConnectToDatabase.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Flows/ConnectToDatabase/ConnectToDatabase.php:11  (return-type)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Flows/ConnectToDatabase/ConnectToDatabase.php:13  (new)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Flows/RunDatabaseQuery/RunDatabaseQuery.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/PublicSurface/DatabaseInterface.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/PublicSurface/DatabaseInterface.php:11  (return-type)

MISSING: Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\BaseConnectionPool  [CRITICAL]
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/ElasticsearchPool.php:14  (extends)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/Neo4jPool.php:14  (extends)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/YugabyteDBPool.php:16  (extends)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/MongoDBPool.php:14  (extends)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/PostgreSQLPool.php:14  (extends)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/CockroachDBPool.php:17  (extends)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/CassandraPool.php:14  (extends)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/MySQLPool.php:9  (extends)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/RetryablePool.php:12  (constructor-param)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/SQLServerPool.php:14  (extends)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/ClickHousePool.php:14  (extends)

MISSING: Avax\Components\DataStack\Database\System\Capabilities\MigrationRunner  [CRITICAL]
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Flows/RunDatabaseMigration/RunDatabaseMigration.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Flows/RunDatabaseMigration/RunDatabaseMigration.php:12  (constructor-param)

MISSING: Avax\Components\DataStack\Database\System\Capabilities\Migrations\Design\BaseMigration  [MINOR]
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/LoadMigrations/MigrationLoader.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/LoadMigrations/MigrationLoader.php:56  (instanceof)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/LoadMigrations/MigrationLoader.php:89  (return-type)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/LoadMigrations/MigrationLoader.php:93  (instanceof)

MISSING: Avax\Components\DataStack\Database\System\Capabilities\ORM\EntityManager  [CRITICAL]
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/ORM/Repositories/EntityRepository.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/ORM/Repositories/EntityRepository.php:21  (constructor-param)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Database.php:10  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Database.php:27  (constructor-param)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Database.php:49  (return-type)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/PublicSurface/Entities.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/PublicSurface/Entities.php:18  (constructor-param)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/DatabaseInterface.php:10  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/DatabaseInterface.php:25  (return-type)

MISSING: Avax\Components\DataStack\Database\System\Capabilities\QueryGovernance\System\Capabilities\Detection\NPlusOneDetector  [MINOR]
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/QueryGovernance/QueryGovernance.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/QueryGovernance/QueryGovernance.php:31  (static-call)

MISSING: Avax\Components\DataStack\Database\System\Capabilities\QueryGovernance\System\Capabilities\Detection\SlowQueryDetector  [MINOR]
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/QueryGovernance/QueryGovernance.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/QueryGovernance/QueryGovernance.php:36  (static-call)

MISSING: Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\BaseGrammar  [CRITICAL]
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Query/Grammar/MySQLGrammar.php:39  (extends)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Query/Grammar/SQLServerGrammar.php:21  (extends)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Query/Grammar/ClickHouseGrammar.php:14  (extends)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Query/Grammar/ElasticsearchGrammar.php:13  (extends)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Query/Grammar/PostgreSQLGrammar.php:20  (extends)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Query/Grammar/Neo4jGrammar.php:13  (extends)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Query/Grammar/MongoDBGrammar.php:14  (extends)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Query/Grammar/SQLiteGrammar.php:20  (extends)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Query/Grammar/RedisGrammar.php:14  (extends)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Query/Grammar/CassandraGrammar.php:14  (extends)

MISSING: Avax\Components\DataStack\Database\System\Capabilities\Transactions\Contracts\TransactionsInterface  [CRITICAL]
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Transactions/RunTransaction/Transaction.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Transactions/RunTransaction/Transaction.php:17  (implements)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Transactions/RunTransaction/TransactionScope.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Transactions/RunTransaction/TransactionScope.php:38  (constructor-param)

MISSING: Avax\Components\DataStack\Database\Telemetry  [MINOR]
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:23  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:55  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Database/Core/KernelTest.php:14  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Database/Core/KernelTest.php:46  (class-const-fetch)

MISSING: Avax\Components\DataStack\Database\Transactions  [MINOR]
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:24  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php:53  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Database/Core/KernelTest.php:15  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Database/Core/KernelTest.php:45  (class-const-fetch)

MISSING: Avax\Components\DataStack\Database\Values\Identity\Uuid  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Values/SemanticValuesTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Values/SemanticValuesTest.php:51  (static-call)

MISSING: Avax\Components\DataStack\Database\Values\Money\Currency  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Values/SemanticValuesTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Values/SemanticValuesTest.php:58  (new)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Values/SemanticValuesTest.php:59  (new)

MISSING: Avax\Components\DataStack\Database\Values\Money\Money  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Values/SemanticValuesTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Values/SemanticValuesTest.php:58  (new)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Values/SemanticValuesTest.php:59  (new)

MISSING: Avax\Components\DataStack\Database\Values\Numbers\PositiveInt  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Values/SemanticValuesTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Values/SemanticValuesTest.php:46  (new)

MISSING: Avax\Components\DataStack\Database\Values\Option\Option  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Values/SemanticValuesTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Values/SemanticValuesTest.php:21  (static-call)

MISSING: Avax\Components\DataStack\Database\Values\Result\Result  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Values/SemanticValuesTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Values/SemanticValuesTest.php:29  (static-call)

MISSING: Avax\Components\DataStack\Database\Values\Text\NonEmptyString  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Values/SemanticValuesTest.php:13  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataFoundation/Values/SemanticValuesTest.php:37  (new)

MISSING: Avax\Components\DataStack\Persistence\AccessPersistentData  [CRITICAL]
  - /home/shomsy/projects/avax/components/DataStack/Persistence/DataLayer.php:12  (property-type)
  - /home/shomsy/projects/avax/components/DataStack/Persistence/DataLayer.php:16  (constructor-param)
  - /home/shomsy/projects/avax/components/DataStack/Persistence/DataLayer.php:26  (new)
  - /home/shomsy/projects/avax/components/DataStack/Persistence/DataLayer.php:32  (return-type)

MISSING: Avax\Components\DataStack\Persistence\CommitDataChanges  [CRITICAL]
  - /home/shomsy/projects/avax/components/DataStack/Persistence/DataLayer.php:13  (property-type)
  - /home/shomsy/projects/avax/components/DataStack/Persistence/DataLayer.php:16  (constructor-param)
  - /home/shomsy/projects/avax/components/DataStack/Persistence/DataLayer.php:27  (new)
  - /home/shomsy/projects/avax/components/DataStack/Persistence/DataLayer.php:37  (return-type)

MISSING: Avax\Components\DataStack\Persistence\DataLayerConfig  [CRITICAL]
  - /home/shomsy/projects/avax/components/DataStack/Persistence/DataLayer.php:14  (property-type)
  - /home/shomsy/projects/avax/components/DataStack/Persistence/DataLayer.php:16  (constructor-param)
  - /home/shomsy/projects/avax/components/DataStack/Persistence/DataLayer.php:42  (return-type)

MISSING: Avax\Components\DataStack\Persistence\RegisterDataLayerRuntime  [CRITICAL]
  - /home/shomsy/projects/avax/components/DataStack/Persistence/DataLayer.php:25  (new)

MISSING: Avax\Components\DataStack\System\Capabilities\DataLayer\AccessPersistentData  [CRITICAL]
  - /home/shomsy/projects/avax/components/DataStack/System/Capabilities/DataLayer/DataLayer.php:13  (constructor-param)
  - /home/shomsy/projects/avax/components/DataStack/System/Capabilities/DataLayer/DataLayer.php:23  (new)
  - /home/shomsy/projects/avax/components/DataStack/System/Capabilities/DataLayer/DataLayer.php:29  (return-type)

MISSING: Avax\Components\DataStack\System\Capabilities\DataLayer\CommitDataChanges  [CRITICAL]
  - /home/shomsy/projects/avax/components/DataStack/System/Capabilities/DataLayer/DataLayer.php:14  (constructor-param)
  - /home/shomsy/projects/avax/components/DataStack/System/Capabilities/DataLayer/DataLayer.php:24  (new)
  - /home/shomsy/projects/avax/components/DataStack/System/Capabilities/DataLayer/DataLayer.php:34  (return-type)

MISSING: Avax\Components\Data\System\Capabilities\DataTransfer\Foundation\AbstractDTO  [CRITICAL]
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/DTO/ConnectionPoolMetrics.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/DTO/ConnectionPoolMetrics.php:34  (extends)

MISSING: Avax\Components\DeveloperTools\Diagnostics\System\Capabilities\ScalingReadiness\ScalingAudit  [CRITICAL]
  - /home/shomsy/projects/avax/components/DeveloperTools/Diagnostics/System/Capabilities/ScalingReadiness/ScalingReadiness.php:16  (return-type)
  - /home/shomsy/projects/avax/components/DeveloperTools/Diagnostics/System/Capabilities/ScalingReadiness/ScalingReadiness.php:18  (new)

MISSING: Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting\System\Capabilities\Verification\BreakingChangeDetector  [CRITICAL]
  - /home/shomsy/projects/avax/components/DeveloperTools/Testing/System/Capabilities/ContractTesting/ContractTesting.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DeveloperTools/Testing/System/Capabilities/ContractTesting/ContractTesting.php:28  (new)

MISSING: Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting\System\Capabilities\Verification\ContractVerifier  [CRITICAL]
  - /home/shomsy/projects/avax/components/DeveloperTools/Testing/System/Capabilities/ContractTesting/ContractTesting.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/DeveloperTools/Testing/System/Capabilities/ContractTesting/ContractTesting.php:14  (new)
  - /home/shomsy/projects/avax/components/DeveloperTools/Testing/System/Capabilities/ContractTesting/ContractTesting.php:21  (new)
  - /home/shomsy/projects/avax/components/DeveloperTools/Testing/System/Capabilities/ContractTesting/ContractTesting.php:35  (new)

MISSING: Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting\System\PublicSurface\BreakingChangesReport  [CRITICAL]
  - /home/shomsy/projects/avax/components/DeveloperTools/Testing/System/Capabilities/ContractTesting/Verification/ContractVerifier.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DeveloperTools/Testing/System/Capabilities/ContractTesting/Verification/ContractVerifier.php:65  (return-type)
  - /home/shomsy/projects/avax/components/DeveloperTools/Testing/System/Capabilities/ContractTesting/Verification/ContractVerifier.php:67  (new)

MISSING: Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting\System\PublicSurface\ComponentContractResult  [CRITICAL]
  - /home/shomsy/projects/avax/components/DeveloperTools/Testing/System/Capabilities/ContractTesting/Verification/ContractVerifier.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/DeveloperTools/Testing/System/Capabilities/ContractTesting/Verification/ContractVerifier.php:32  (return-type)
  - /home/shomsy/projects/avax/components/DeveloperTools/Testing/System/Capabilities/ContractTesting/Verification/ContractVerifier.php:37  (new)
  - /home/shomsy/projects/avax/components/DeveloperTools/Testing/System/Capabilities/ContractTesting/Verification/ContractVerifier.php:55  (new)

MISSING: Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting\System\PublicSurface\ContractVerificationReport  [CRITICAL]
  - /home/shomsy/projects/avax/components/DeveloperTools/Testing/System/Capabilities/ContractTesting/Verification/ContractVerifier.php:9  (use-statement)
  - /home/shomsy/projects/avax/components/DeveloperTools/Testing/System/Capabilities/ContractTesting/Verification/ContractVerifier.php:21  (return-type)
  - /home/shomsy/projects/avax/components/DeveloperTools/Testing/System/Capabilities/ContractTesting/Verification/ContractVerifier.php:29  (new)

MISSING: Avax\Components\Filesystem\Disks\Local\LocalDisk  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/AppendToFileTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/AppendToFileTest.php:13  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/AppendToFileTest.php:20  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/ReadFileLastModifiedAtTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/ReadFileLastModifiedAtTest.php:13  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/ReadFileLastModifiedAtTest.php:20  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/FilesystemTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/FilesystemTest.php:13  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/FilesystemTest.php:65  (new)

MISSING: Avax\Components\Filesystem\Files\AppendToFile  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/AppendToFileTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/AppendToFileTest.php:34  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/AppendToFileTest.php:46  (new)

MISSING: Avax\Components\Filesystem\Files\ReadFileLastModifiedAt  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/ReadFileLastModifiedAtTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/ReadFileLastModifiedAtTest.php:32  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/ReadFileLastModifiedAtTest.php:41  (new)

MISSING: Avax\Components\Filesystem\Filesystem  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/FilesystemTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/FilesystemTest.php:15  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/FilesystemTest.php:66  (new)

MISSING: Avax\Components\Filesystem\Paths\ChangePathPermissions  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Paths/ChangePathPermissionsTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Paths/ChangePathPermissionsTest.php:31  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Paths/ChangePathPermissionsTest.php:41  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Paths/ChangePathPermissionsTest.php:50  (new)

MISSING: Avax\Components\Filesystem\Paths\PathExists  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Paths/PathExistsTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Paths/PathExistsTest.php:32  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Paths/PathExistsTest.php:41  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Paths/PathExistsTest.php:50  (new)

MISSING: Avax\Components\Filesystem\Paths\PathIsDirectory  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Paths/PathIsDirectoryTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Paths/PathIsDirectoryTest.php:35  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Paths/PathIsDirectoryTest.php:42  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Paths/PathIsDirectoryTest.php:49  (new)

MISSING: Avax\Components\Filesystem\Paths\PathIsWritable  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Paths/PathIsWritableTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Paths/PathIsWritableTest.php:29  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Paths/PathIsWritableTest.php:36  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Paths/PathIsWritableTest.php:47  (new)

MISSING: Avax\Components\Filesystem\System\Capabilities\Storage\StorageInterface  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/PublicSurface/FilesystemStorage.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/PublicSurface/FilesystemStorage.php:18  (property-type)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/PublicSurface/FilesystemStorage.php:20  (param-type)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/PublicSurface/FilesystemStorage.php:30  (return-type)
  - /home/shomsy/projects/avax/components/Application/Filesystem/System/PublicSurface/FilesystemStorage.php:32  (instanceof)

MISSING: Avax\Components\GracefulShutdown\System\Capabilities\ShutdownSequence  [CRITICAL]
  - /home/shomsy/projects/avax/framework/System/Capabilities/Runtime/GracefulShutdown/System/PublicSurface/GracefulShutdown.php:7  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Capabilities/Runtime/GracefulShutdown/System/PublicSurface/GracefulShutdown.php:18  (static-call)
  - /home/shomsy/projects/avax/framework/System/Capabilities/Runtime/GracefulShutdown/System/PublicSurface/GracefulShutdown.php:19  (static-call)
  - /home/shomsy/projects/avax/framework/System/Capabilities/Runtime/GracefulShutdown/System/PublicSurface/GracefulShutdown.php:22  (return-type)
  - /home/shomsy/projects/avax/framework/System/Capabilities/Runtime/GracefulShutdown/System/PublicSurface/GracefulShutdown.php:24  (new)
  - /home/shomsy/projects/avax/framework/System/Capabilities/Runtime/GracefulShutdown/System/PublicSurface/GracefulShutdown.php:29  (static-call)

MISSING: Avax\Components\HTTP\AfterResponse\System\Capabilities\Tasks\AfterResponseTask\AfterResponseQueue  [CRITICAL]
  - /home/shomsy/projects/avax/components/HTTP/AfterResponse/System/PublicSurface/AfterResponse.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/AfterResponse/System/PublicSurface/AfterResponse.php:13  (property-type)
  - /home/shomsy/projects/avax/components/HTTP/AfterResponse/System/PublicSurface/AfterResponse.php:20  (return-type)
  - /home/shomsy/projects/avax/components/HTTP/AfterResponse/System/PublicSurface/AfterResponse.php:23  (new)

MISSING: Avax\Components\HTTP\AppKernel  [CRITICAL]
  - /home/shomsy/projects/avax/components/HTTP/RouterBootstrapper.php:266  (return-type)
  - /home/shomsy/projects/avax/components/HTTP/RouterBootstrapper.php:273  (new)

MISSING: Avax\Components\HTTP\ContentNegotiation\System\Capabilities\Formats\SimpleXMLElement  [CRITICAL]
  - /home/shomsy/projects/avax/components/HTTP/ContentNegotiation/System/Capabilities/Formats/XmlFormat.php:12  (new)
  - /home/shomsy/projects/avax/components/HTTP/ContentNegotiation/System/Capabilities/Formats/XmlFormat.php:21  (param-type)

MISSING: Avax\Components\HTTP\Context\GlobalsProviderInterface  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Context/HttpContextTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Context/HttpContextTest.php:42  (return-type)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Context/HttpContextTest.php:46  (implements)

MISSING: Avax\Components\HTTP\Context\HttpContext  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Context/HttpContextTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Context/HttpContextTest.php:30  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Context/HttpContextTest.php:82  (new)

MISSING: Avax\Components\HTTP\Dispatcher\ControllerDispatcher  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:34  (property-type)
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:183  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslAnyTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslAnyTest.php:131  (class-const-fetch)

MISSING: Avax\Components\HTTP\Enums\HttpMethod  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:27  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:37  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:38  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:46  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:47  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:55  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:57  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:78  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:91  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:94  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:105  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:106  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:107  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:123  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:135  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:136  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:145  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:146  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:147  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:148  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:149  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:150  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:151  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:152  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/HttpMethodCompletenessTest.php:153  (class-const-fetch)

MISSING: Avax\Components\HTTP\Kernel  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/KernelContractTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/KernelContractTest.php:28  (class-const-fetch)

MISSING: Avax\Components\HTTP\Middleware\CorsMiddleware  [MINOR]
  - /home/shomsy/projects/avax/examples/minimal-http-app/config/middleware.php:13  (use-statement)
  - /home/shomsy/projects/avax/examples/minimal-http-app/config/middleware.php:31  (class-const-fetch)

MISSING: Avax\Components\HTTP\Middleware\ExceptionHandlerMiddleware  [MINOR]
  - /home/shomsy/projects/avax/examples/minimal-http-app/config/middleware.php:14  (use-statement)
  - /home/shomsy/projects/avax/examples/minimal-http-app/config/middleware.php:24  (class-const-fetch)

MISSING: Avax\Components\HTTP\Middleware\JsonResponseMiddleware  [MINOR]
  - /home/shomsy/projects/avax/examples/minimal-http-app/config/middleware.php:15  (use-statement)
  - /home/shomsy/projects/avax/examples/minimal-http-app/config/middleware.php:33  (class-const-fetch)

MISSING: Avax\Components\HTTP\Middleware\SecurityHeadersMiddleware  [MINOR]
  - /home/shomsy/projects/avax/examples/minimal-http-app/config/middleware.php:18  (use-statement)
  - /home/shomsy/projects/avax/examples/minimal-http-app/config/middleware.php:25  (class-const-fetch)

MISSING: Avax\Components\HTTP\Middleware\System\Capabilities\Pipeline\MiddlewarePipeline  [CRITICAL]
  - /home/shomsy/projects/avax/components/HTTP/Middleware/System/Flows/RunMiddlewarePipeline/RunMiddlewarePipeline.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Middleware/System/Flows/RunMiddlewarePipeline/RunMiddlewarePipeline.php:14  (constructor-param)
  - /home/shomsy/projects/avax/components/HTTP/System/Configuration/HttpProvider.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/System/Configuration/HttpProvider.php:18  (new)
  - /home/shomsy/projects/avax/components/HTTP/System/Configuration/HttpBuilder.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/System/Configuration/HttpBuilder.php:17  (new)
  - /home/shomsy/projects/avax/components/HTTP/System/PublicSurface/Http.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/System/PublicSurface/Http.php:14  (constructor-param)

MISSING: Avax\Components\HTTP\Request\AbsoluteServerRequest  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/Characterization/AbsoluteServerRequestTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/Characterization/AbsoluteServerRequestTest.php:25  (return-type)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/Characterization/AbsoluteServerRequestTest.php:39  (new)

MISSING: Avax\Components\HTTP\Request\ParameterBag  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:111  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:112  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:113  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:114  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Capability/Input/InputAccessorTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Capability/Input/InputAccessorTest.php:122  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Capability/Input/InputAccessorTest.php:123  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Capability/Input/InputAccessorTest.php:124  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Capability/Input/InputAccessorTest.php:125  (new)

MISSING: Avax\Components\HTTP\Request\Request  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterContractTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterContractTest.php:84  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterContractTest.php:116  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterContractTest.php:162  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Regression/Foundation/HTTP/Request/RequestRegressionTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Regression/Foundation/HTTP/Request/RequestRegressionTest.php:39  (return-type)
  - /home/shomsy/projects/avax/tests/Regression/Foundation/HTTP/Request/RequestRegressionTest.php:41  (new)
  - /home/shomsy/projects/avax/tests/Regression/Foundation/HTTP/Request/RequestRegressionTest.php:109  (class-const-fetch)

MISSING: Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\AssembleIncomingRequest  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:131  (new)

MISSING: Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\Configuration\PrepareRequest  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:132  (new)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:7  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:34  (property-type)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:40  (new)

MISSING: Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ProtocolVersion\NormalizeProtocolVersion  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:137  (new)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:8  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:45  (new)

MISSING: Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestAttributes\RequestAttributes  [CRITICAL]
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:9  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:76  (new)

MISSING: Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\ParsedBody  [CRITICAL]
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:10  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:75  (new)

MISSING: Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseBodyByContentType  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:13  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:133  (new)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:11  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:41  (new)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:111  (new)

MISSING: Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseFormBody  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:14  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:135  (new)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:12  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:43  (new)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:113  (new)

MISSING: Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseJsonBody  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:15  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:134  (new)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:13  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:42  (new)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:112  (new)

MISSING: Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\RequestBody  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/Foundation/HTTP/Request/RequestCharacterizationTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/HTTP/Request/RequestCharacterizationTest.php:35  (new)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/HTTP/Request/RequestCharacterizationTest.php:71  (new)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:14  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:62  (new)

MISSING: Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestCookies\RequestCookies  [CRITICAL]
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:15  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:72  (new)

MISSING: Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestHeaders\RequestHeaders  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/Foundation/HTTP/Request/RequestHeaders/RequestHeadersTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/HTTP/Request/RequestHeaders/RequestHeadersTest.php:17  (new)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/HTTP/Request/RequestHeaders/RequestHeadersTest.php:24  (new)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/HTTP/Request/RequestHeaders/RequestHeadersTest.php:31  (new)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/HTTP/Request/RequestHeaders/RequestHeadersTest.php:39  (new)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/HTTP/Request/RequestHeaders/RequestHeadersTest.php:47  (new)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/HTTP/Request/RequestHeaders/RequestHeadersTest.php:54  (new)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/HTTP/Request/RequestHeaders/RequestHeadersTest.php:64  (new)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/HTTP/Request/RequestHeaders/RequestHeadersTest.php:74  (new)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/HTTP/Request/RequestHeaders/RequestHeadersTest.php:83  (new)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:18  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:69  (new)

MISSING: Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestInit  [MINOR]
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:19  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:61  (static-call)

MISSING: Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestTarget\ReadRequestTarget  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/Foundation/HTTP/Request/RequestTarget/ReadRequestTargetTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/HTTP/Request/RequestTarget/ReadRequestTargetTest.php:19  (new)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/HTTP/Request/RequestTarget/ReadRequestTargetTest.php:27  (new)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/HTTP/Request/RequestTarget/ReadRequestTargetTest.php:35  (new)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/HTTP/Request/RequestTarget/ReadRequestTargetTest.php:43  (new)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/HTTP/Request/RequestTarget/ReadRequestTargetTest.php:52  (new)

MISSING: Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs\Examples\UserRegistrationDTO  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/ServerRequest/IncomingRequest/Inputs/Examples/UserRegistrationDTOTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/ServerRequest/IncomingRequest/Inputs/Examples/UserRegistrationDTOTest.php:15  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/ServerRequest/IncomingRequest/Inputs/Examples/UserRegistrationDTOTest.php:32  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/ServerRequest/IncomingRequest/Inputs/Examples/UserRegistrationDTOTest.php:43  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/ServerRequest/IncomingRequest/Inputs/Examples/UserRegistrationDTOTest.php:54  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/ServerRequest/IncomingRequest/Inputs/Examples/UserRegistrationDTOTest.php:65  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/ServerRequest/IncomingRequest/Inputs/Examples/UserRegistrationDTOTest.php:75  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/ServerRequest/IncomingRequest/Inputs/Examples/UserRegistrationDTOTest.php:86  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/ServerRequest/IncomingRequest/Inputs/Examples/UserRegistrationDTOTest.php:98  (new)

MISSING: Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Mapping\MapRequestedInputsToDto  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:16  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:146  (new)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:16  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:86  (new)

MISSING: Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Sanitization\InputSanitizer  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:17  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:145  (new)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:17  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:85  (new)

MISSING: Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerInit  [CRITICAL]
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:20  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:82  (new)

MISSING: Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\NormalizeUploadedFiles  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:18  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:138  (new)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:22  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:46  (new)

MISSING: Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\UploadedFiles  [CRITICAL]
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:23  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:74  (new)

MISSING: Avax\Components\HTTP\Request\ServerRequest\Network\ParseForwardedAddresses  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:19  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:142  (new)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:24  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:50  (new)

MISSING: Avax\Components\HTTP\Request\ServerRequest\Network\ResolveClientAddress  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:20  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:140  (new)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:25  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:48  (new)

MISSING: Avax\Components\HTTP\Request\ServerRequest\Network\TrustedIpv4ProxyPolicy  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:21  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:139  (new)
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:141  (new)

MISSING: Avax\Components\HTTP\Request\ServerRequest\Network\TrustedProxyPolicy  [CRITICAL]
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:26  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:38  (new)

MISSING: Avax\Components\HTTP\Request\System\Capability\Input\InputAccessor  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:110  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Capability/Input/InputAccessorTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Capability/Input/InputAccessorTest.php:13  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Capability/Input/InputAccessorTest.php:127  (new)

MISSING: Avax\Components\HTTP\Request\System\Capability\Input\JsonBodyParser  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:121  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Capability/Input/JsonBodyParserTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Capability/Input/JsonBodyParserTest.php:13  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Capability/Input/JsonBodyParserTest.php:73  (new)

MISSING: Avax\Components\HTTP\Request\System\Capability\SessionBridge\RequestSessionBridge  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:117  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Capability/SessionBridge/RequestSessionBridgeTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Capability/SessionBridge/RequestSessionBridgeTest.php:14  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Capability/SessionBridge/RequestSessionBridgeTest.php:111  (new)

MISSING: Avax\Components\HTTP\Request\System\Flow\ReadRequest\ReadRequest  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:19  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:123  (new)

MISSING: Avax\Components\HTTP\Request\System\Flow\ReadRequest\ReadRequestData  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:23  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:45  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:61  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:77  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:93  (new)

MISSING: Avax\Components\HTTP\Request\System\Flow\ReadRequest\ReadRequestResult  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:13  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:34  (class-const-fetch)

MISSING: Avax\Components\HTTP\Request\System\Request  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:20  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:86  (return-type)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:90  (new)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:20  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:66  (return-type)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:70  (new)
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:120  (param-type)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/RequestFromGlobalsTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/RequestFromGlobalsTest.php:64  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/RequestFromGlobalsTest.php:66  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/RequestFromGlobalsTest.php:70  (param-type)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/RequestFromGlobalsTest.php:72  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/RequestFromGlobalsTest.php:83  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RegexConstraintTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RegexConstraintTest.php:38  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RegexConstraintTest.php:61  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RegexConstraintTest.php:84  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RegexConstraintTest.php:106  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RegexConstraintTest.php:131  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RegexConstraintTest.php:154  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RegexConstraintTest.php:180  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RegexConstraintTest.php:212  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslAnyTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslAnyTest.php:108  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:41  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:65  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:92  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:117  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:141  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:160  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:180  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:205  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:213  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:281  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/RouteResolutionTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/RouteResolutionTest.php:46  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/RouteResolutionTest.php:76  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/Characterization/RequestBehaviorTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/Characterization/RequestBehaviorTest.php:23  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/Characterization/RequestBehaviorTest.php:26  (return-type)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/Characterization/RequestBehaviorTest.php:36  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/Characterization/RequestBehaviorTest.php:180  (static-call)
  - /home/shomsy/projects/avax/tests/fixtures/routes_with_null_callable.php:5  (use-statement)

MISSING: Avax\Components\HTTP\Request\System\RequestDtoFactory  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:156  (new)
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:158  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:162  (class-const-fetch)

MISSING: Avax\Components\HTTP\Response\Capabilities\Caching\CacheControl  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/ResponseFacadeTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/ResponseFacadeTest.php:79  (new)

MISSING: Avax\Components\HTTP\Response\Capabilities\Caching\Etag  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/ResponseFacadeTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/ResponseFacadeTest.php:77  (new)

MISSING: Avax\Components\HTTP\Response\Capabilities\Caching\LastModified  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/ResponseFacadeTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/ResponseFacadeTest.php:78  (new)

MISSING: Avax\Components\HTTP\Response\Capabilities\Cookies\ResponseCookie  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/Capabilities/ResponseHeadersTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/Capabilities/ResponseHeadersTest.php:40  (new)

MISSING: Avax\Components\HTTP\Response\Capabilities\Cookies\SetCookieHeader  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/Capabilities/ResponseHeadersTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/Capabilities/ResponseHeadersTest.php:38  (new)

MISSING: Avax\Components\HTTP\Response\Capabilities\Headers\ResponseHeaders  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/Capabilities/ResponseHeadersTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/Capabilities/ResponseHeadersTest.php:18  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/Capabilities/ResponseHeadersTest.php:33  (new)

MISSING: Avax\Components\HTTP\Response\Capabilities\Streams\ResponseStreamFactory  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/Foundation/HTTP/Request/RequestCharacterizationTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/HTTP/Request/RequestCharacterizationTest.php:35  (new)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/HTTP/Request/RequestCharacterizationTest.php:71  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/Capabilities/ResponseStreamFactoryTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/Capabilities/ResponseStreamFactoryTest.php:14  (new)
  - /home/shomsy/projects/avax/tests/Regression/Foundation/HTTP/Request/RequestRegressionTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Regression/Foundation/HTTP/Request/RequestRegressionTest.php:45  (new)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:27  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:36  (constructor-param)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:36  (new)

MISSING: Avax\Components\HTTP\Response\Flows\BuildResponse\BuildEmptyResponse  [CRITICAL]
  - /home/shomsy/projects/avax/components/HTTP/Response/System/PublicSurface/Responses.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Response/System/PublicSurface/Responses.php:72  (new)

MISSING: Avax\Components\HTTP\Response\Flows\BuildResponse\BuildHtmlResponse  [CRITICAL]
  - /home/shomsy/projects/avax/components/HTTP/Response/System/PublicSurface/Responses.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Response/System/PublicSurface/Responses.php:85  (new)

MISSING: Avax\Components\HTTP\Response\Flows\BuildResponse\BuildJsonResponse  [CRITICAL]
  - /home/shomsy/projects/avax/components/HTTP/Response/System/PublicSurface/Responses.php:9  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Response/System/PublicSurface/Responses.php:48  (new)

MISSING: Avax\Components\HTTP\Response\Flows\BuildResponse\BuildRedirectResponse  [CRITICAL]
  - /home/shomsy/projects/avax/components/HTTP/Response/System/PublicSurface/Responses.php:10  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Response/System/PublicSurface/Responses.php:80  (new)

MISSING: Avax\Components\HTTP\Response\Flows\BuildResponse\BuildResponse  [CRITICAL]
  - /home/shomsy/projects/avax/components/HTTP/Response/System/PublicSurface/Responses.php:11  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Response/System/PublicSurface/Responses.php:58  (new)

MISSING: Avax\Components\HTTP\Response\Flows\BuildResponse\BuildTextResponse  [CRITICAL]
  - /home/shomsy/projects/avax/components/HTTP/Response/System/PublicSurface/Responses.php:12  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Response/System/PublicSurface/Responses.php:53  (new)

MISSING: Avax\Components\HTTP\Response\Flows\EmitResponse\EmitResponse  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/Flows/EmitResponseTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/Flows/EmitResponseTest.php:19  (new)

MISSING: Avax\Components\HTTP\Response\Response  [MINOR]
  - /home/shomsy/projects/avax/components/Presentation/View/System/PublicSurface/shortcuts.php:9  (use-statement)
  - /home/shomsy/projects/avax/components/Presentation/View/System/PublicSurface/shortcuts.php:25  (static-call)
  - /home/shomsy/projects/avax/components/HTTP/Response/System/PublicSurface/Responses.php:13  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Response/System/PublicSurface/Responses.php:39  (static-call)
  - /home/shomsy/projects/avax/components/HTTP/Response/System/PublicSurface/Responses.php:40  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:22  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/ControllerDispatcherUnitTest.php:126  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/HttpApplicationTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/HttpApplicationTest.php:92  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/RouteResolutionTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/RouteResolutionTest.php:34  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/RouteResolutionTest.php:64  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/Capabilities/ResponseHeadersTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/Capabilities/ResponseHeadersTest.php:39  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/Flows/EmitResponseTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/Flows/EmitResponseTest.php:19  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/ResponseFacadeTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/ResponseFacadeTest.php:18  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/ResponseFacadeTest.php:19  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/ResponseFacadeTest.php:20  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/ResponseFacadeTest.php:45  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/ResponseFacadeTest.php:50  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/ResponseFacadeTest.php:57  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/ResponseFacadeTest.php:75  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Response/ResponseFacadeTest.php:76  (static-call)
  - /home/shomsy/projects/avax/tests/fixtures/routes_with_null_callable.php:6  (use-statement)
  - /home/shomsy/projects/avax/tests/fixtures/routes_with_null_callable.php:22  (static-call)

MISSING: Avax\Components\HTTP\Response\Responses  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/HTTP/Response/ResponseFactoryTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/HTTP/Response/ResponseFactoryTest.php:16  (property-type)
  - /home/shomsy/projects/avax/tests/Unit/HTTP/Response/ResponseFactoryTest.php:205  (new)

MISSING: Avax\Components\HTTP\Response\System\Flows\CreateJsonResponse\CreateJsonResponse  [CRITICAL]
  - /home/shomsy/projects/avax/components/HTTP/System/Flows/HandleRequest/CatchUnhandledExceptions.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/System/Flows/HandleRequest/CatchUnhandledExceptions.php:16  (constructor-param)

MISSING: Avax\Components\HTTP\RouteRegistrarProxy  [MINOR]
  - /home/shomsy/projects/avax/components/HTTP/RouterBootstrapper.php:125  (return-type)

MISSING: Avax\Components\HTTP\Router\HttpMethod  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteAttributeRegistrarTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteAttributeRegistrarTest.php:43  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteAttributeRegistrarTest.php:44  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteAttributeRegistrarTest.php:46  (class-const-fetch)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/FrameworkRouteRegistrar.php:7  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/FrameworkRouteRegistrar.php:25  (class-const-fetch)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/FrameworkRouteRegistrar.php:30  (class-const-fetch)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/FrameworkRouteRegistrar.php:35  (class-const-fetch)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/FrameworkRouteRegistrar.php:40  (class-const-fetch)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/FrameworkRouteRegistrar.php:45  (class-const-fetch)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/FrameworkRouteRegistrar.php:50  (class-const-fetch)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/FrameworkRouteRegistrar.php:55  (class-const-fetch)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/FrameworkRouteRegistrar.php:60  (class-const-fetch)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/FrameworkRouteRegistrar.php:67  (static-call)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/FrameworkRouteRegistrar.php:68  (class-const-fetch)

MISSING: Avax\Components\HTTP\Router\Router  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterContractTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterContractTest.php:48  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterContractTest.php:160  (class-const-fetch)

MISSING: Avax\Components\HTTP\Router\RouterInterface  [MINOR]
  - /home/shomsy/projects/avax/tests/Integration/Framework/HandleIncomingHttpIntegrationTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterContractTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterContractTest.php:25  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterContractTest.php:175  (class-const-fetch)

MISSING: Avax\Components\HTTP\Router\RouterRuntimeInterface  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:21  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:98  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:21  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:78  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteCacheTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteCacheTest.php:52  (implements)

MISSING: Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteDefinition  [MINOR]
  - /home/shomsy/projects/avax/components/HTTP/Router/System/Capabilities/RouteCollection/RouteCollection.php:12  (param-type)

MISSING: Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteCollection  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslAnyTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslAnyTest.php:126  (new)

MISSING: Avax\Components\HTTP\Router\System\Flows\BootstrapRoutes\Cache\RouteCacheLoader  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteCacheTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteCacheTest.php:76  (new)

MISSING: Avax\Components\HTTP\Router\System\Flows\BootstrapRoutes\Cache\RouteCacheManifest  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/CacheManifestIntegrityTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/CacheManifestIntegrityTest.php:37  (return-type)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/CacheManifestIntegrityTest.php:43  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/CacheManifestIntegrityTest.php:140  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/CacheManifestIntegrityTest.php:160  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/CacheManifestIntegrityTest.php:167  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/CacheManifestIntegrityTest.php:189  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/CacheManifestIntegrityTest.php:214  (static-call)

MISSING: Avax\Components\HTTP\Router\System\Flows\BootstrapRoutes\Cache\RouteExportValidator  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteExportValidatorTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteExportValidatorTest.php:18  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteExportValidatorTest.php:274  (new)

MISSING: Avax\Components\HTTP\Router\System\Flows\BootstrapRoutes\State\RouterBootstrapState  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterBootstrapConcurrencyTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterBootstrapConcurrencyTest.php:17  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterBootstrapConcurrencyTest.php:89  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterBootstrapConcurrencyTest.php:90  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterBootstrapConcurrencyTest.php:111  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteBootstrapperTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteBootstrapperTest.php:47  (new)

MISSING: Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Attributes\AttributeRouteRegistrar  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteAttributeRegistrarTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteAttributeRegistrarTest.php:36  (new)

MISSING: Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Attributes\Route  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteAttributeRegistrarTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteAttributeRegistrarTest.php:19  (attribute)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteAttributeRegistrarTest.php:22  (attribute)

MISSING: Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouteBuilder  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteValidationCentralizationTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteValidationCentralizationTest.php:81  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteValidationCentralizationTest.php:97  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteValidationCentralizationTest.php:110  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteValidationCentralizationTest.php:122  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteValidationCentralizationTest.php:144  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslIntegrationTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslIntegrationTest.php:30  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslIntegrationTest.php:33  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslIntegrationTest.php:45  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslIntegrationTest.php:81  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslIntegrationTest.php:106  (static-call)

MISSING: Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RoutePathValidator  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:49  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:63  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:74  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:85  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:96  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:107  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:118  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:129  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:140  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:151  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:159  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:168  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:177  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:178  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:186  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:187  (static-call)

MISSING: Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouteRegistry  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteCacheTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteCacheTest.php:77  (new)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/FrameworkRouteRegistrar.php:11  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/FrameworkRouteRegistrar.php:19  (constructor-param)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/FrameworkRouteRegistrar.php:19  (new)

MISSING: Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouterRegistrar  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteCacheTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteCacheTest.php:77  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslAnyTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslAnyTest.php:129  (class-const-fetch)

MISSING: Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Fallback\RegisteredFallback  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslAnyTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslAnyTest.php:132  (class-const-fetch)

MISSING: Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Files\RouteRegistrarProxy  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslAnyTest.php:13  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslAnyTest.php:40  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslAnyTest.php:56  (class-const-fetch)

MISSING: Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Groups\RouteGroupContext  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteGroupStackTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteGroupStackTest.php:35  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteGroupStackTest.php:36  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteGroupStackTest.php:65  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteGroupStackTest.php:66  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteGroupStackTest.php:91  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteGroupStackTest.php:107  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteGroupStackTest.php:108  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteGroupStackTest.php:137  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslIntegrationTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslIntegrationTest.php:62  (new)

MISSING: Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Groups\RouteGroupFrames  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteGroupStackTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteGroupStackTest.php:18  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteGroupStackTest.php:134  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteGroupStackTest.php:135  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteGroupStackTest.php:153  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslIntegrationTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslIntegrationTest.php:43  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslIntegrationTest.php:57  (new)

MISSING: Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\RouterDsl  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslAnyTest.php:14  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslAnyTest.php:29  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslAnyTest.php:128  (new)

MISSING: Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Constraints\RouteConstraintValidator  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RegexConstraintTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RegexConstraintTest.php:21  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RegexConstraintTest.php:222  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteCacheTest.php:13  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteCacheTest.php:46  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteAttributeRegistrarTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteAttributeRegistrarTest.php:35  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:357  (new)

MISSING: Avax\Components\HTTP\Router\System\Flows\ResolveRequest\HttpRequestRouter  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteCacheTest.php:14  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteCacheTest.php:45  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteCacheTest.php:53  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteCacheTest.php:55  (constructor-param)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteAttributeRegistrarTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteAttributeRegistrarTest.php:35  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslAnyTest.php:15  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslAnyTest.php:125  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:25  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:356  (new)

MISSING: Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Matching\DomainAwareMatcher  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/DomainAwareMatcherTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/DomainAwareMatcherTest.php:19  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/DomainAwareMatcherTest.php:254  (new)

MISSING: Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Matching\DomainPatternCompiler  [MINOR]
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/MatchHttpRoute.php:7  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/MatchHttpRoute.php:119  (static-call)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/MatchHttpRoute.php:121  (static-call)

MISSING: Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Matching\RouteMatcher  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteAttributeRegistrarTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteAttributeRegistrarTest.php:35  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslAnyTest.php:16  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslAnyTest.php:85  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/DomainAwareMatcherTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/DomainAwareMatcherTest.php:21  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/DomainAwareMatcherTest.php:253  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/RouteResolutionTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/RouteResolutionTest.php:23  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/RouteResolutionTest.php:88  (new)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/MatchHttpRoute.php:8  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/MatchHttpRoute.php:18  (property-type)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/MatchHttpRoute.php:20  (constructor-param)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/MatchHttpRoute.php:22  (new)

MISSING: Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Matching\RouteMatcherRegistry  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteCacheTest.php:15  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteCacheTest.php:42  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:353  (static-call)

MISSING: Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Request\RouteRequestInjector  [MINOR]
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/MatchHttpRoute.php:11  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/MatchHttpRoute.php:36  (static-call)

MISSING: Avax\Components\HTTP\Router\System\Flows\RunRoute\Pipeline\RouteStage  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/SampleStage.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/SampleStage.php:12  (implements)

MISSING: Avax\Components\HTTP\Router\System\Foundation\Exceptions\DuplicateRouteException  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteCacheTest.php:16  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteAttributeRegistrarTest.php:13  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:11  (use-statement)

MISSING: Avax\Components\HTTP\Router\System\Foundation\Exceptions\InvalidConstraintException  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RegexConstraintTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RegexConstraintTest.php:86  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RegexConstraintTest.php:109  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RegexConstraintTest.php:214  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:12  (use-statement)

MISSING: Avax\Components\HTTP\Router\System\Foundation\Exceptions\InvalidRouteException  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:13  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:228  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:243  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:258  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:342  (class-const-fetch)

MISSING: Avax\Components\HTTP\Router\System\Foundation\Exceptions\MethodNotAllowedException  [CRITICAL]
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/MatchHttpRoute.php:12  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/MatchHttpRoute.php:76  (static-call)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ConfiguredRoutesHttpHandler.php:12  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ConfiguredRoutesHttpHandler.php:102  (catch)

MISSING: Avax\Components\HTTP\Router\System\Foundation\Exceptions\ReservedRouteNameException  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Integration/AppKernelIntegrationTest.php:15  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RegexConstraintTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteValidationCentralizationTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteValidationCentralizationTest.php:157  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterContractTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteCacheTest.php:17  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteAttributeRegistrarTest.php:14  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteExportValidatorTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/ReservedRouteNameTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/ReservedRouteNameTest.php:57  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/ReservedRouteNameTest.php:89  (catch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslAnyTest.php:17  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslIntegrationTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:14  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/RouteResolutionTest.php:11  (use-statement)

MISSING: Avax\Components\HTTP\Router\System\Foundation\Exceptions\RouteNotFoundException  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:15  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Routing/HttpRouterRoutingTest.php:218  (class-const-fetch)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/MatchHttpRoute.php:13  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/MatchHttpRoute.php:83  (static-call)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ConfiguredRoutesHttpHandler.php:13  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ConfiguredRoutesHttpHandler.php:90  (catch)

MISSING: Avax\Components\HTTP\Session\Audit\Audit  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Session/Capabilities/AuditCharacterizationTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Session/Capabilities/AuditCharacterizationTest.php:64  (new)

MISSING: Avax\Components\HTTP\Session\Recovery\Recovery  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Session/Security/SessionImportSecurityTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Session/Security/SessionImportSecurityTest.php:58  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Session/Capabilities/RecoveryCharacterizationTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Session/Capabilities/RecoveryCharacterizationTest.php:61  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Session/Capabilities/RecoveryCharacterizationTest.php:142  (new)

MISSING: Avax\Components\HTTP\Session\SessionAudit\SessionAudit  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Session/Capabilities/SessionAuditTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Session/Capabilities/SessionAuditTest.php:14  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Session/Capabilities/SessionAuditTest.php:32  (new)

MISSING: Avax\Components\HTTP\Session\SessionSecurity\SessionPolicy\AbsoluteLifetimePolicy  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Session/Security/SessionPolicyTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Session/Security/SessionPolicyTest.php:38  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Session/Security/SessionPolicyTest.php:49  (new)

MISSING: Avax\Components\HTTP\Session\SessionSecurity\SessionPolicy\IdleTimeoutPolicy  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Session/Security/SessionPolicyTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Session/Security/SessionPolicyTest.php:16  (new)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Session/Security/SessionPolicyTest.php:27  (new)

MISSING: Avax\Components\HTTP\Session\SessionSecurity\SessionPolicy\SecureTransportPolicy  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Session/Security/SessionPolicyTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Session/Security/SessionPolicyTest.php:60  (new)

MISSING: Avax\Components\HTTP\Session\Shared\Contracts\SessionInterface  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/RequestFromGlobalsTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/RequestFromGlobalsTest.php:35  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/RequestFromGlobalsTest.php:40  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/RequestFromGlobalsTest.php:70  (return-type)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:14  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:42  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Flow/ReadRequest/ReadRequestTest.php:118  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Capability/SessionBridge/RequestSessionBridgeTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Capability/SessionBridge/RequestSessionBridgeTest.php:16  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Capability/SessionBridge/RequestSessionBridgeTest.php:46  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/System/Capability/SessionBridge/RequestSessionBridgeTest.php:110  (class-const-fetch)

MISSING: Avax\Components\HTTP\Session\Shared\Contracts\Storage\StoreInterface  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Session/Security/SessionImportSecurityTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Session/Security/SessionImportSecurityTest.php:15  (implements)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Session/Capabilities/RecoveryCharacterizationTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Session/Capabilities/RecoveryCharacterizationTest.php:18  (implements)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Session/Capabilities/RecoveryCharacterizationTest.php:99  (implements)

MISSING: Avax\Components\HTTP\System\Capabilities\Request  [CRITICAL]
  - /home/shomsy/projects/avax/components/HTTP/System/Flows/CreateRequestFromGlobals/CreateRequestFromGlobals.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/System/Flows/CreateRequestFromGlobals/CreateRequestFromGlobals.php:12  (return-type)
  - /home/shomsy/projects/avax/components/HTTP/System/Flows/CreateRequestFromGlobals/CreateRequestFromGlobals.php:19  (new)

MISSING: Avax\Components\HTTP\System\Capabilities\Uri  [CRITICAL]
  - /home/shomsy/projects/avax/components/HTTP/System/Flows/CreateRequestFromGlobals/CreateRequestFromGlobals.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/System/Flows/CreateRequestFromGlobals/CreateRequestFromGlobals.php:28  (return-type)
  - /home/shomsy/projects/avax/components/HTTP/System/Flows/CreateRequestFromGlobals/CreateRequestFromGlobals.php:36  (new)

MISSING: Avax\Components\Identity\Access\System\Capabilities\Policy\Capabilities\Engine\DecisionExplanation  [CRITICAL]
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/Capabilities/Engine/PolicyEvaluator.php:19  (return-type)
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/Capabilities/Engine/PolicyEvaluator.php:39  (new)

MISSING: Avax\Components\Identity\Access\System\Capabilities\Policy\Capabilities\Rules\PolicyDecision  [CRITICAL]
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/Capabilities/Rules/PolicyRule.php:22  (return-type)
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/Capabilities/Rules/PolicyRule.php:27  (new)
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/Capabilities/Rules/PolicyRule.php:31  (new)

MISSING: Avax\Components\Identity\Access\System\Capabilities\Policy\Rules\PolicyDecision  [CRITICAL]
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/Rules/PolicyRule.php:22  (return-type)
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/Rules/PolicyRule.php:27  (new)
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/Rules/PolicyRule.php:31  (new)

MISSING: Avax\Components\Identity\Access\System\Capabilities\Policy\System\Capabilities\Engine\PolicyEvaluator  [CRITICAL]
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/PublicSurface/Policy.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/PublicSurface/Policy.php:12  (property-type)
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/PublicSurface/Policy.php:41  (return-type)
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/PublicSurface/Policy.php:44  (new)
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/Policy.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/Policy.php:12  (property-type)
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/Policy.php:41  (return-type)
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/Policy.php:44  (new)

MISSING: Avax\Components\Identity\Access\System\Capabilities\Policy\System\Capabilities\Rules\PolicyRule  [MINOR]
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/Engine/PolicyEvaluator.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/Engine/PolicyEvaluator.php:14  (param-type)
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/Capabilities/Engine/PolicyEvaluator.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/Capabilities/Engine/PolicyEvaluator.php:14  (param-type)
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/PublicSurface/Policy.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/PublicSurface/Policy.php:50  (param-type)
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/Policy.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Access/System/Capabilities/Policy/Policy.php:50  (param-type)

MISSING: Avax\Components\Identity\Auth\Integrations\Http\HttpOAuthProofInput  [CRITICAL]
  - /home/shomsy/projects/avax/components/Identity/Auth/examples/http/VerifySenderConstrainedRequest.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Auth/examples/http/VerifySenderConstrainedRequest.php:49  (new)

MISSING: Avax\Components\Identity\Auth\Integrations\Http\VerifyDpopProof  [CRITICAL]
  - /home/shomsy/projects/avax/components/Identity/Auth/examples/http/VerifySenderConstrainedRequest.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Auth/examples/http/VerifySenderConstrainedRequest.php:41  (new)

MISSING: Avax\Components\Identity\Auth\Integrations\Http\VerifyMtlsSenderConstraint  [CRITICAL]
  - /home/shomsy/projects/avax/components/Identity/Auth/examples/http/VerifySenderConstrainedRequest.php:9  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Auth/examples/http/VerifySenderConstrainedRequest.php:46  (new)

MISSING: Avax\Components\Identity\Auth\Integrations\Http\VerifyOAuthSenderConstraint  [CRITICAL]
  - /home/shomsy/projects/avax/components/Identity/Auth/examples/http/VerifySenderConstrainedRequest.php:10  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Auth/examples/http/VerifySenderConstrainedRequest.php:40  (new)

MISSING: Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSource  [CRITICAL]
  - /home/shomsy/projects/avax/components/Identity/Auth/System/Flows/Register/CreateRegisteredUser.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Auth/System/Flows/Register/CreateRegisteredUser.php:17  (constructor-param)
  - /home/shomsy/projects/avax/components/Identity/Auth/System/Flows/Login/FindUserByCredentials.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Auth/System/Flows/Login/FindUserByCredentials.php:17  (constructor-param)

MISSING: Avax\Components\Identity\Auth\System\Foundation\Failure\AuthFailure  [CRITICAL]
  - /home/shomsy/projects/avax/components/Identity/Auth/System/Flows/Logout/LogoutFailed.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Auth/System/Flows/Logout/LogoutFailed.php:13  (extends)

MISSING: Avax\Components\Identity\Sessions\System\PublicSurface\Session  [MINOR]
  - /home/shomsy/projects/avax/components/compat.php:34  (use-statement)
  - /home/shomsy/projects/avax/components/compat.php:157  (class-const-fetch)

MISSING: Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\System\Capabilities\Signing\JwtSigner  [CRITICAL]
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/JwtAuth.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/JwtAuth.php:16  (property-type)
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/JwtAuth.php:24  (new)
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/JwtAuth.php:45  (return-type)

MISSING: Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\System\Capabilities\Tokens\AccessToken  [MINOR]
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/Verification/TokenVerifier.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/Verification/TokenVerifier.php:19  (return-type)
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/Verification/TokenVerifier.php:23  (static-call)
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/JwtAuth.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/JwtAuth.php:29  (return-type)

MISSING: Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\System\Capabilities\Tokens\TokenPair  [CRITICAL]
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/JwtAuth.php:9  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/JwtAuth.php:54  (return-type)
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/JwtAuth.php:79  (return-type)
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/JwtAuth.php:106  (new)

MISSING: Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\System\Capabilities\Verification\TokenVerifier  [CRITICAL]
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/JwtAuth.php:10  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/JwtAuth.php:18  (property-type)
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/JwtAuth.php:25  (new)
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/JwtAuth.php:40  (return-type)

MISSING: Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\Verification\JwtSigner  [CRITICAL]
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/Verification/TokenVerifier.php:15  (constructor-param)

MISSING: Avax\Components\Logging\ErrorHandler  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/var/cache/Avax_Logging_ErrorHandler.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/var/cache/Avax_Logging_ErrorHandler.php:11  (class-const-fetch)

MISSING: Avax\Components\Logging\ErrorLogger  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/var/cache/Avax_Logging_ErrorLogger.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/var/cache/Avax_Logging_ErrorLogger.php:11  (class-const-fetch)

MISSING: Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Orchestration\System\Capabilities\Kubernetes\GracefulStop  [MINOR]
  - /home/shomsy/projects/avax/components/Operations/ApplicationWorkflow/System/Capabilities/Orchestration/Orchestration.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Operations/ApplicationWorkflow/System/Capabilities/Orchestration/Orchestration.php:40  (static-call)

MISSING: Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga\TenantBoundary  [CRITICAL]
  - /home/shomsy/projects/avax/components/Operations/ApplicationWorkflow/System/Flows/Saga/DefineSaga/SagaDefinition.php:53  (constructor-param)
  - /home/shomsy/projects/avax/components/Operations/ApplicationWorkflow/System/Flows/Saga/DefineSaga/SagaDefinition.php:108  (static-call)

MISSING: Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState\RuntimeException  [CRITICAL]
  - /home/shomsy/projects/avax/components/Operations/ApplicationWorkflow/System/Flows/Saga/StoreSagaState/StoreSagaState.php:119  (new)
  - /home/shomsy/projects/avax/components/Operations/ApplicationWorkflow/System/Flows/Saga/StoreSagaState/StoreSagaState.php:124  (new)

MISSING: Avax\Components\Operations\Events\System\Capabilities\EventDispatcherInterface  [MINOR]
  - /home/shomsy/projects/avax/components/Operations/Events/System/Configuration/RegisterEventDependencies.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Operations/Events/System/Configuration/RegisterEventDependencies.php:12  (param-type)

MISSING: Avax\Components\Operations\Filesystem\System\Capabilities\Adapters\LocalStorageAdapter  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Integration/Components/StorageTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/Components/StorageTest.php:14  (new)
  - /home/shomsy/projects/avax/tests/Integration/Components/ComponentIntegrationTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/Components/ComponentIntegrationTest.php:57  (new)
  - /home/shomsy/projects/avax/tests/Integration/Components/ComponentIntegrationTest.php:73  (new)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/StorageUnitTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/StorageUnitTest.php:24  (return-type)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/StorageUnitTest.php:26  (new)

MISSING: Avax\Components\Operations\Filesystem\System\Capabilities\Adapters\S3StorageAdapter  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/StorageUnitTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/StorageUnitTest.php:81  (new)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/StorageUnitTest.php:88  (new)

MISSING: Avax\Components\Operations\Filesystem\System\Capabilities\Adapters\Storage  [MINOR]
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/StorageUnitTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/StorageUnitTest.php:98  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/StorageUnitTest.php:100  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/StorageUnitTest.php:102  (static-call)

MISSING: Avax\Components\Operations\Filesystem\System\Capabilities\Drivers\LocalStorageAdapter  [CRITICAL]
  - /home/shomsy/projects/avax/components/Operations/Filesystem/System/Capabilities/Drivers/Filesystem.php:39  (new)

MISSING: Avax\Components\Operations\Filesystem\System\Capabilities\Drivers\S3StorageAdapter  [CRITICAL]
  - /home/shomsy/projects/avax/components/Operations/Filesystem/System/Capabilities/Drivers/Filesystem.php:38  (new)

MISSING: Avax\Components\Operations\Filesystem\System\Capabilities\Drivers\StorageAdapter  [CRITICAL]
  - /home/shomsy/projects/avax/components/Operations/Filesystem/System/Capabilities/Drivers/S3.php:7  (implements)
  - /home/shomsy/projects/avax/components/Operations/Filesystem/System/Capabilities/Drivers/Local.php:11  (implements)
  - /home/shomsy/projects/avax/components/Operations/Filesystem/System/Capabilities/Drivers/Filesystem.php:12  (param-type)
  - /home/shomsy/projects/avax/components/Operations/Filesystem/System/Capabilities/Drivers/Filesystem.php:22  (return-type)
  - /home/shomsy/projects/avax/components/Operations/Filesystem/System/Capabilities/Drivers/Filesystem.php:33  (return-type)

MISSING: Avax\Components\Operations\MessageBus\System\Capabilities\Bus\Closure  [MINOR]
  - /home/shomsy/projects/avax/components/Operations/MessageBus/System/Capabilities/Bus/CommandBus.php:37  (return-type)

MISSING: Avax\Components\Operations\MessageBus\System\Capabilities\Bus\Command  [MINOR]
  - /home/shomsy/projects/avax/components/Operations/MessageBus/System/Capabilities/Bus/CommandBus.php:23  (param-type)
  - /home/shomsy/projects/avax/components/Operations/MessageBus/System/Capabilities/Bus/CommandBus.php:50  (param-type)

MISSING: Avax\Components\Operations\MessageBus\System\Capabilities\Bus\RuntimeException  [CRITICAL]
  - /home/shomsy/projects/avax/components/Operations/MessageBus/System/Capabilities/Bus/QueryBus.php:28  (new)
  - /home/shomsy/projects/avax/components/Operations/MessageBus/System/Capabilities/Bus/CommandBus.php:28  (new)

MISSING: Avax\Components\Operations\Notifications\System\Configuration\NotificationChannel  [MINOR]
  - /home/shomsy/projects/avax/components/Operations/Notifications/System/Configuration/RegisterNotificationDependencies.php:11  (param-type)

MISSING: Avax\Components\Operations\Queue\System\Capabilities\TaskDispatch\System\Capabilities\Dispatchers\AsyncDispatcher  [CRITICAL]
  - /home/shomsy/projects/avax/components/Operations/Queue/System/Capabilities/TaskDispatch/TaskDispatch.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Operations/Queue/System/Capabilities/TaskDispatch/TaskDispatch.php:48  (new)

MISSING: Avax\Components\Operations\Queue\System\Capabilities\TaskDispatch\System\Capabilities\Dispatchers\DeferredDispatcher  [CRITICAL]
  - /home/shomsy/projects/avax/components/Operations/Queue/System/Capabilities/TaskDispatch/TaskDispatch.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/Operations/Queue/System/Capabilities/TaskDispatch/TaskDispatch.php:54  (new)
  - /home/shomsy/projects/avax/components/Operations/Queue/System/Capabilities/TaskDispatch/TaskBatch.php:10  (use-statement)

MISSING: Avax\Components\Operations\Queue\System\Capabilities\TaskDispatch\System\Capabilities\Dispatchers\SyncDispatcher  [CRITICAL]
  - /home/shomsy/projects/avax/components/Operations/Queue/System/Capabilities/TaskDispatch/TaskDispatch.php:9  (use-statement)
  - /home/shomsy/projects/avax/components/Operations/Queue/System/Capabilities/TaskDispatch/TaskDispatch.php:42  (new)

MISSING: Avax\Components\Operations\Queue\System\Capabilities\TaskDispatch\System\Capabilities\Resolution\DispatchStrategyResolver  [CRITICAL]
  - /home/shomsy/projects/avax/components/Operations/Queue/System/Capabilities/TaskDispatch/TaskDispatch.php:10  (use-statement)
  - /home/shomsy/projects/avax/components/Operations/Queue/System/Capabilities/TaskDispatch/TaskDispatch.php:16  (property-type)
  - /home/shomsy/projects/avax/components/Operations/Queue/System/Capabilities/TaskDispatch/TaskDispatch.php:31  (return-type)
  - /home/shomsy/projects/avax/components/Operations/Queue/System/Capabilities/TaskDispatch/TaskDispatch.php:34  (new)

MISSING: Avax\Components\Operations\Realtime\System\Capabilities\Channels\ChannelManager  [CRITICAL]
  - /home/shomsy/projects/avax/components/Operations/Realtime/System/Capabilities/Channels/Channel.php:13  (constructor-param)
  - /home/shomsy/projects/avax/components/Operations/Realtime/System/PublicSurface/Realtime.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/Operations/Realtime/System/PublicSurface/Realtime.php:18  (property-type)
  - /home/shomsy/projects/avax/components/Operations/Realtime/System/PublicSurface/Realtime.php:42  (return-type)
  - /home/shomsy/projects/avax/components/Operations/Realtime/System/PublicSurface/Realtime.php:44  (instanceof)
  - /home/shomsy/projects/avax/components/Operations/Realtime/System/PublicSurface/Realtime.php:45  (new)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/RealtimeUnitTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/RealtimeUnitTest.php:52  (new)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/RealtimeUnitTest.php:66  (new)

MISSING: Avax\Components\Operations\Resilience\System\Capabilities\Fallback\System\PublicSurface\Fallback  [MINOR]
  - /home/shomsy/projects/avax/tests/Integration/GoldenPath/GoldenPathTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/GoldenPath/GoldenPathTest.php:72  (static-call)

MISSING: Avax\Components\Operations\Resilience\System\Capabilities\Idempotency\InMemoryIdempotencyStore  [CRITICAL]
  - /home/shomsy/projects/avax/components/Operations/Resilience/System/Capabilities/Idempotency/Idempotency.php:22  (new)

MISSING: Avax\Components\Operations\Resilience\System\Capabilities\Idempotency\System\Capabilities\Keys\IdempotencyStore  [MINOR]
  - /home/shomsy/projects/avax/components/Operations/Resilience/System/Capabilities/Idempotency/Idempotency.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Operations/Resilience/System/Capabilities/Idempotency/Idempotency.php:12  (property-type)
  - /home/shomsy/projects/avax/components/Operations/Resilience/System/Capabilities/Idempotency/Idempotency.php:19  (return-type)

MISSING: Avax\Components\Operations\Tasks\System\Capabilities\Queue\Queue  [MINOR]
  - /home/shomsy/projects/avax/components/Operations/Mail/System/Capabilities/Queue/MailQueue.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Operations/Mail/System/Capabilities/Queue/MailQueue.php:13  (static-call)
  - /home/shomsy/projects/avax/components/Operations/Mail/System/Capabilities/Queue/MailQueue.php:22  (static-call)
  - /home/shomsy/projects/avax/components/Operations/Mail/System/Capabilities/Queue/MailQueue.php:39  (static-call)
  - /home/shomsy/projects/avax/tests/Integration/Components/QueueTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/Components/QueueTest.php:15  (static-call)
  - /home/shomsy/projects/avax/tests/Integration/Components/QueueTest.php:19  (static-call)
  - /home/shomsy/projects/avax/tests/Integration/Components/QueueTest.php:28  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/MailQueueUnitTest.php:16  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/MailQueueUnitTest.php:62  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/MailQueueUnitTest.php:110  (static-call)

MISSING: Avax\Components\Operations\Tasks\System\Capabilities\TaskBus  [CRITICAL]
  - /home/shomsy/projects/avax/components/Operations/Queue/System/PublicSurface/Tasks.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Operations/Queue/System/PublicSurface/Tasks.php:14  (new)
  - /home/shomsy/projects/avax/components/Operations/Queue/System/PublicSurface/Tasks.php:20  (new)
  - /home/shomsy/projects/avax/components/Operations/Queue/System/PublicSurface/Tasks.php:41  (new)

MISSING: Avax\Components\Operations\Tasks\System\Foundation\JobInterface  [MINOR]
  - /home/shomsy/projects/avax/components/Operations/Queue/System/Capabilities/Queue/Queue.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Operations/Queue/System/Capabilities/Queue/Queue.php:15  (param-type)
  - /home/shomsy/projects/avax/components/Operations/Queue/System/Capabilities/Queue/Queue.php:31  (param-type)
  - /home/shomsy/projects/avax/components/Operations/Queue/System/Capabilities/Queue/Queue.php:107  (instanceof)

MISSING: Avax\Components\Persistence\System\Capabilities\Repositories\Repository  [MINOR]
  - /home/shomsy/projects/avax/components/DataStack/Persistence/AccessPersistentData/AccessPersistentData.php:6  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Persistence/DataLayer.php:6  (use-statement)
  - /home/shomsy/projects/avax/components/DataLayer/AccessPersistentData/AccessPersistentData.php:5  (use-statement)
  - /home/shomsy/projects/avax/components/DataLayer/DataLayer.php:5  (use-statement)

MISSING: Avax\Components\Persistence\System\Capabilities\UnitOfWork\UnitOfWork  [MINOR]
  - /home/shomsy/projects/avax/components/DataStack/Persistence/DataLayer.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DataLayer/DataLayer.php:6  (use-statement)

MISSING: Avax\Components\Persistence\System\Capabilities\UnitOfWork\UnitOfWorkInterface  [MINOR]
  - /home/shomsy/projects/avax/components/DataStack/Persistence/CommitDataChanges/CommitDataChanges.php:6  (use-statement)
  - /home/shomsy/projects/avax/components/DataLayer/CommitDataChanges/CommitDataChanges.php:5  (use-statement)

MISSING: Avax\Components\Persistence\System\Configuration\PersistenceBuilder  [MINOR]
  - /home/shomsy/projects/avax/components/DataStack/Persistence/ConfigureDataLayer/RegisterDataLayerRuntime.php:6  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Persistence/ConfigureDataLayer/ResolveDataLayerRuntime.php:6  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Persistence/DataLayer.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/DataLayer/ConfigureDataLayer/RegisterDataLayerRuntime.php:5  (use-statement)
  - /home/shomsy/projects/avax/components/DataLayer/ConfigureDataLayer/ResolveDataLayerRuntime.php:5  (use-statement)
  - /home/shomsy/projects/avax/components/DataLayer/DataLayer.php:7  (use-statement)

MISSING: Avax\Components\Persistence\System\Foundation\Failure\PersistenceFailure  [CRITICAL]
  - /home/shomsy/projects/avax/components/DataStack/Persistence/CommitDataChanges/CommitDataChanges.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Persistence/AccessPersistentData/AccessPersistentData.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/DataStack/Persistence/AccessPersistentData/AccessPersistentData.php:42  (new)
  - /home/shomsy/projects/avax/components/DataStack/Persistence/AccessPersistentData/AccessPersistentData.php:49  (new)
  - /home/shomsy/projects/avax/components/DataStack/Persistence/AccessPersistentData/AccessPersistentData.php:62  (new)
  - /home/shomsy/projects/avax/components/DataLayer/CommitDataChanges/CommitDataChanges.php:6  (use-statement)
  - /home/shomsy/projects/avax/components/DataLayer/AccessPersistentData/AccessPersistentData.php:6  (use-statement)

MISSING: Avax\Components\Presentation\View\System\Capabilities\TemplateEngineInterface  [MINOR]
  - /home/shomsy/projects/avax/components/Presentation/View/System/Configuration/RegisterViewDependencies.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Presentation/View/System/Configuration/RegisterViewDependencies.php:13  (param-type)

MISSING: Avax\Components\Presentation\Views\System\PublicSurface\View  [MINOR]
  - /home/shomsy/projects/avax/components/compat.php:35  (use-statement)
  - /home/shomsy/projects/avax/components/compat.php:159  (class-const-fetch)

MISSING: Avax\Components\Request\System\PublicSurface\ServerRequest  [MINOR]
  - /home/shomsy/projects/avax/components/HTTP/Security/System/Flows/VerifyCsrfToken/VerifyCsrfToken.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Security/System/Flows/VerifyCsrfToken/VerifyCsrfToken.php:20  (param-type)
  - /home/shomsy/projects/avax/components/HTTP/Security/System/Flows/VerifyCsrfToken/VerifyCsrfToken.php:35  (param-type)
  - /home/shomsy/projects/avax/components/HTTP/Session/System/Flows/Lifecycle/SessionLifecycleMiddleware.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Session/System/Flows/Lifecycle/SessionLifecycleMiddleware.php:17  (param-type)

MISSING: Avax\Components\ResourceGovernor\System\Capabilities\Memory\MemoryBudget  [MINOR]
  - /home/shomsy/projects/avax/framework/System/Capabilities/ResourceGovernance/System/PublicSurface/ResourceGovernor.php:7  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Capabilities/ResourceGovernance/System/PublicSurface/ResourceGovernor.php:13  (property-type)
  - /home/shomsy/projects/avax/framework/System/Capabilities/ResourceGovernance/System/PublicSurface/ResourceGovernor.php:29  (static-call)
  - /home/shomsy/projects/avax/framework/System/Capabilities/ResourceGovernance/System/PublicSurface/ResourceGovernor.php:61  (instanceof)

MISSING: Avax\Components\ResourceGovernor\System\Capabilities\Memory\MemorySnapshot  [CRITICAL]
  - /home/shomsy/projects/avax/framework/System/Capabilities/ResourceGovernance/System/PublicSurface/ResourceGovernor.php:8  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Capabilities/ResourceGovernance/System/PublicSurface/ResourceGovernor.php:46  (new)

MISSING: Avax\Components\Response\System\PublicSurface\Response  [CRITICAL]
  - /home/shomsy/projects/avax/components/HTTP/Security/System/Flows/VerifyCsrfToken/VerifyCsrfToken.php:9  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Security/System/Flows/VerifyCsrfToken/VerifyCsrfToken.php:49  (return-type)
  - /home/shomsy/projects/avax/components/HTTP/Security/System/Flows/VerifyCsrfToken/VerifyCsrfToken.php:51  (new)

MISSING: Avax\Components\Security\System\System\Capabilities\Audit\SecurityAuditLog  [CRITICAL]
  - /home/shomsy/projects/avax/components/Security/System/PublicSurface/Security.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Security/System/PublicSurface/Security.php:19  (property-type)
  - /home/shomsy/projects/avax/components/Security/System/PublicSurface/Security.php:99  (return-type)
  - /home/shomsy/projects/avax/components/Security/System/PublicSurface/Security.php:101  (instanceof)
  - /home/shomsy/projects/avax/components/Security/System/PublicSurface/Security.php:102  (new)

MISSING: Avax\Components\Security\System\System\Capabilities\Csrf\CsrfToken  [MINOR]
  - /home/shomsy/projects/avax/components/Security/System/PublicSurface/Security.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/Security/System/PublicSurface/Security.php:23  (static-call)
  - /home/shomsy/projects/avax/components/Security/System/PublicSurface/Security.php:28  (static-call)

MISSING: Avax\Components\Security\System\System\Capabilities\Csrf\CsrfVerifier  [MINOR]
  - /home/shomsy/projects/avax/components/Security/System/PublicSurface/Security.php:9  (use-statement)
  - /home/shomsy/projects/avax/components/Security/System/PublicSurface/Security.php:33  (static-call)

MISSING: Avax\Components\Security\System\System\Capabilities\Escape\OutputEscaper  [MINOR]
  - /home/shomsy/projects/avax/components/Security/System/PublicSurface/Security.php:10  (use-statement)
  - /home/shomsy/projects/avax/components/Security/System/PublicSurface/Security.php:41  (static-call)
  - /home/shomsy/projects/avax/components/Security/System/PublicSurface/Security.php:46  (static-call)
  - /home/shomsy/projects/avax/components/Security/System/PublicSurface/Security.php:51  (static-call)

MISSING: Avax\Components\Security\System\System\Capabilities\Headers\SecurityHeaders  [MINOR]
  - /home/shomsy/projects/avax/components/Security/System/PublicSurface/Security.php:11  (use-statement)
  - /home/shomsy/projects/avax/components/Security/System/PublicSurface/Security.php:61  (static-call)

MISSING: Avax\Components\Security\System\System\Capabilities\MassAssignment\MassAssignmentGuard  [CRITICAL]
  - /home/shomsy/projects/avax/components/Security/System/PublicSurface/Security.php:12  (use-statement)
  - /home/shomsy/projects/avax/components/Security/System/PublicSurface/Security.php:56  (new)

MISSING: Avax\Components\Security\System\System\Capabilities\SignedUrls\SignedUrlGenerator  [MINOR]
  - /home/shomsy/projects/avax/components/Security/System/PublicSurface/Security.php:13  (use-statement)
  - /home/shomsy/projects/avax/components/Security/System/PublicSurface/Security.php:66  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/DocumentationMonitoringSecurityPerformanceUnitTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/DocumentationMonitoringSecurityPerformanceUnitTest.php:99  (static-call)

MISSING: Avax\Components\Security\System\System\Capabilities\SignedUrls\SignedUrlVerifier  [MINOR]
  - /home/shomsy/projects/avax/components/Security/System/PublicSurface/Security.php:14  (use-statement)
  - /home/shomsy/projects/avax/components/Security/System/PublicSurface/Security.php:71  (static-call)

MISSING: Avax\Components\Security\System\System\PublicSurface\ResponseFormatter  [CRITICAL]
  - /home/shomsy/projects/avax/components/HTTP/Security/System/Capabilities/Headers/SecurityHeaders.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Security/System/Capabilities/Headers/SecurityHeaders.php:11  (param-type)
  - /home/shomsy/projects/avax/components/HTTP/Security/System/Capabilities/Headers/SecurityHeaders.php:11  (return-type)
  - /home/shomsy/projects/avax/components/HTTP/Security/System/Capabilities/Headers/SecurityHeaders.php:22  (param-type)
  - /home/shomsy/projects/avax/components/HTTP/Security/System/Capabilities/Headers/SecurityHeaders.php:22  (return-type)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/DocumentationMonitoringSecurityPerformanceUnitTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/DocumentationMonitoringSecurityPerformanceUnitTest.php:92  (new)

MISSING: Avax\Components\Security\System\System\PublicSurface\Security  [MINOR]
  - /home/shomsy/projects/avax/tests/Integration/GoldenPath/GoldenPathTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/GoldenPath/GoldenPathTest.php:97  (static-call)
  - /home/shomsy/projects/avax/tests/Integration/GoldenPath/GoldenPathTest.php:99  (static-call)
  - /home/shomsy/projects/avax/tests/Integration/GoldenPath/GoldenPathTest.php:100  (static-call)
  - /home/shomsy/projects/avax/tests/Integration/GoldenPath/GoldenPathTest.php:108  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/DocumentationMonitoringSecurityPerformanceUnitTest.php:13  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/DocumentationMonitoringSecurityPerformanceUnitTest.php:61  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/DocumentationMonitoringSecurityPerformanceUnitTest.php:66  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/DocumentationMonitoringSecurityPerformanceUnitTest.php:73  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/DocumentationMonitoringSecurityPerformanceUnitTest.php:78  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/DocumentationMonitoringSecurityPerformanceUnitTest.php:83  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/DocumentationMonitoringSecurityPerformanceUnitTest.php:84  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/DocumentationMonitoringSecurityPerformanceUnitTest.php:92  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/DocumentationMonitoringSecurityPerformanceUnitTest.php:101  (static-call)

MISSING: Avax\Components\Server\System\Capabilities\PhpBuiltInServer  [MINOR]
  - /home/shomsy/projects/avax/tests/Integration/Components/ComponentIntegrationTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/Components/ComponentIntegrationTest.php:19  (static-call)
  - /home/shomsy/projects/avax/tests/Integration/Components/ComponentIntegrationTest.php:27  (static-call)

MISSING: Avax\Components\StatelessBoundary\System\Capabilities\Enforcement\StatelessGuard  [MINOR]
  - /home/shomsy/projects/avax/framework/System/Capabilities/RuntimeSafety/StatelessBoundary/System/PublicSurface/StatelessBoundary.php:7  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Capabilities/RuntimeSafety/StatelessBoundary/System/PublicSurface/StatelessBoundary.php:26  (static-call)

MISSING: Avax\Components\Text\Pattern  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Text/PatternFlagsPropagationTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Text/PatternFlagsPropagationTest.php:14  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Text/PatternFlagsPropagationTest.php:22  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Text/PatternFlagsPropagationTest.php:29  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Text/PatternFlagsPropagationTest.php:35  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Text/PatternFlagsPropagationTest.php:42  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Text/MatchResultGroupsTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Text/MatchResultGroupsTest.php:14  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Text/MatchResultGroupsTest.php:23  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Text/MatchResultGroupsTest.php:30  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Text/MatchResultGroupsTest.php:38  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Text/MatchResultGroupsTest.php:46  (static-call)

MISSING: Avax\Components\View\BladeTemplateEngine  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/ViewServiceProviderTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/ViewServiceProviderTest.php:51  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/ViewServiceProviderTest.php:54  (class-const-fetch)

MISSING: Avax\Components\WorkerManager\System\Capabilities\Workers\WorkerProcess  [CRITICAL]
  - /home/shomsy/projects/avax/framework/System/Capabilities/WorkerManagement/System/PublicSurface/WorkerPool.php:7  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Capabilities/WorkerManagement/System/PublicSurface/WorkerPool.php:58  (new)

MISSING: Avax\Config\Architecture\DDD\AppPath  [MINOR]
  - /home/shomsy/projects/avax/examples/minimal-http-app/config/views.php:7  (use-statement)
  - /home/shomsy/projects/avax/examples/minimal-http-app/config/views.php:18  (static-call)
  - /home/shomsy/projects/avax/examples/minimal-http-app/config/views.php:19  (class-const-fetch)
  - /home/shomsy/projects/avax/examples/minimal-http-app/config/filesystems.php:5  (use-statement)
  - /home/shomsy/projects/avax/examples/minimal-http-app/config/filesystems.php:13  (static-call)

MISSING: Avax\Container\ContainerInterface  [MINOR]
  - /home/shomsy/projects/avax/components/compat.php:162  (class-const-fetch)

MISSING: Avax\DataFoundation\ObjectHandling\DTO\AbstractDTO  [MINOR]
  - /home/shomsy/projects/avax/components/compat.php:47  (class-const-fetch)

MISSING: Avax\DataHandling\DataTransfer\Capabilities\Attributes\CastWith  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:9  (use-statement)

MISSING: Avax\DataHandling\DataTransfer\Capabilities\Attributes\Hidden  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:10  (use-statement)

MISSING: Avax\DataHandling\DataTransfer\Capabilities\Attributes\ListOf  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:11  (use-statement)

MISSING: Avax\DataHandling\DataTransfer\Capabilities\Attributes\MapFrom  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:12  (use-statement)

MISSING: Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferFailure  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/Capabilities/ErrorReporting/DataTransferViolationsTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/Capabilities/ErrorReporting/DataTransferViolationsTest.php:65  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferResultTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferResultTest.php:32  (new)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferResultTest.php:47  (new)

MISSING: Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferViolation  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/Capabilities/ErrorReporting/DataTransferViolationsTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/Capabilities/ErrorReporting/DataTransferViolationsTest.php:29  (new)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/Capabilities/ErrorReporting/DataTransferViolationsTest.php:30  (new)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/Capabilities/ErrorReporting/DataTransferViolationsTest.php:31  (new)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/Capabilities/ErrorReporting/DataTransferViolationsTest.php:51  (new)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/Capabilities/ErrorReporting/DataTransferViolationsTest.php:52  (new)

MISSING: Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferViolations  [MINOR]
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/Capabilities/ErrorReporting/DataTransferViolationsTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/Capabilities/ErrorReporting/DataTransferViolationsTest.php:17  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/Capabilities/ErrorReporting/DataTransferViolationsTest.php:28  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/Capabilities/ErrorReporting/DataTransferViolationsTest.php:50  (static-call)

MISSING: Avax\DataHandling\DataTransfer\Capabilities\ValueConversion\ValueCasterInterface  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:13  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:225  (implements)

MISSING: Avax\DataHandling\DataTransfer\DataTransfer  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:14  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:38  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:54  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:63  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:76  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:85  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:86  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:87  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:104  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:124  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:176  (static-call)

MISSING: Avax\DataHandling\DataTransfer\DataTransferException  [MINOR]
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferResultTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferResultTest.php:51  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferResultTest.php:64  (class-const-fetch)

MISSING: Avax\DataHandling\DataTransfer\DataTransferResult  [MINOR]
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferResultTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferResultTest.php:21  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferResultTest.php:35  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferResultTest.php:46  (static-call)
  - /home/shomsy/projects/avax/tests/Unit/Foundation/DataHandling/DataTransfer/DataTransferResultTest.php:61  (static-call)

MISSING: Avax\DataHandling\ObjectHandling\DTO\AbstractDTO  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:15  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:233  (extends)

MISSING: Avax\DataHandling\ObjectHandling\DTO\DTOValidationException  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:16  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:161  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/ServerRequest/IncomingRequest/Inputs/Examples/UserRegistrationDTOTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/ServerRequest/IncomingRequest/Inputs/Examples/UserRegistrationDTOTest.php:30  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/ServerRequest/IncomingRequest/Inputs/Examples/UserRegistrationDTOTest.php:41  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/ServerRequest/IncomingRequest/Inputs/Examples/UserRegistrationDTOTest.php:52  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/ServerRequest/IncomingRequest/Inputs/Examples/UserRegistrationDTOTest.php:63  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Request/ServerRequest/IncomingRequest/Inputs/Examples/UserRegistrationDTOTest.php:104  (catch)

MISSING: Avax\DataHandling\Validation\Attributes\Rules\EmailRule  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:17  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:235  (attribute)

MISSING: Avax\DataHandling\Validation\Attributes\Rules\MinLengthRule  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:18  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php:238  (attribute)

MISSING: Avax\Docs\Components\Api\Capabilities\OpenApi\OpenApiGenerator  [CRITICAL]
  - /home/shomsy/projects/avax/docs/Components/PublicSurface/ApiDocumentation.php:7  (use-statement)
  - /home/shomsy/projects/avax/docs/Components/PublicSurface/ApiDocumentation.php:17  (new)

MISSING: Avax\Docs\Components\Api\Capabilities\Swagger\SwaggerUi  [CRITICAL]
  - /home/shomsy/projects/avax/docs/Components/PublicSurface/ApiDocumentation.php:8  (use-statement)
  - /home/shomsy/projects/avax/docs/Components/PublicSurface/ApiDocumentation.php:22  (new)

MISSING: Avax\Facade\Facades\Route  [MINOR]
  - /home/shomsy/projects/avax/examples/minimal-http-app/app/HTTP/routes/web.routes.php:5  (use-statement)
  - /home/shomsy/projects/avax/examples/minimal-http-app/app/HTTP/routes/web.routes.php:20  (static-call)
  - /home/shomsy/projects/avax/examples/minimal-http-app/app/HTTP/routes/web.routes.php:23  (static-call)
  - /home/shomsy/projects/avax/examples/minimal-http-app/app/HTTP/routes/web.routes.php:26  (static-call)
  - /home/shomsy/projects/avax/examples/minimal-http-app/app/HTTP/routes/web.routes.php:30  (static-call)
  - /home/shomsy/projects/avax/examples/minimal-http-app/app/HTTP/routes/web.routes.php:36  (static-call)
  - /home/shomsy/projects/avax/tests/fixtures/routes_with_null_callable.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/fixtures/routes_with_null_callable.php:10  (static-call)
  - /home/shomsy/projects/avax/tests/fixtures/routes_with_null_callable.php:15  (static-call)

MISSING: Avax\Filesystem\Configuration\FilesystemConfig  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Disks/ResolveDiskTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Disks/ResolveDiskTest.php:40  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Configuration/FilesystemConfigTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Configuration/FilesystemConfigTest.php:14  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Configuration/FilesystemConfigTest.php:22  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Configuration/FilesystemConfigTest.php:31  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Configuration/FilesystemConfigTest.php:40  (new)

MISSING: Avax\Filesystem\Directories\ClearDirectory  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Directories/ClearDirectoryTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Directories/ClearDirectoryTest.php:39  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Directories/ClearDirectoryTest.php:46  (new)

MISSING: Avax\Filesystem\Directories\CreateDirectory  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Directories/CreateDirectoryTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Directories/CreateDirectoryTest.php:40  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Directories/CreateDirectoryTest.php:50  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Directories/CreateDirectoryTest.php:61  (new)

MISSING: Avax\Filesystem\Directories\DeleteDirectory  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Directories/DeleteDirectoryTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Directories/DeleteDirectoryTest.php:36  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Directories/DeleteDirectoryTest.php:45  (new)

MISSING: Avax\Filesystem\Directories\DirectoryClearFailed  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Directories/ClearDirectoryTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Directories/ClearDirectoryTest.php:37  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Disks/Local/LocalDiskTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Disks/Local/LocalDiskTest.php:156  (class-const-fetch)

MISSING: Avax\Filesystem\Directories\DirectoryCreateFailed  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Directories/CreateDirectoryTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Directories/CreateDirectoryTest.php:59  (class-const-fetch)

MISSING: Avax\Filesystem\Directories\EnsureDirectoryExists  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Directories/EnsureDirectoryExistsTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Directories/EnsureDirectoryExistsTest.php:35  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Directories/EnsureDirectoryExistsTest.php:45  (new)

MISSING: Avax\Filesystem\Directories\EnsureDirectoryIsWritable  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Directories/EnsureDirectoryIsWritableTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Directories/EnsureDirectoryIsWritableTest.php:19  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Directories/EnsureDirectoryIsWritableTest.php:36  (new)

MISSING: Avax\Filesystem\Directories\ListDirectoryFiles  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Directories/ListDirectoryFilesTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Directories/ListDirectoryFilesTest.php:36  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Directories/ListDirectoryFilesTest.php:46  (new)

MISSING: Avax\Filesystem\Disks\InvalidDiskDriver  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Disks/ResolveDiskTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Disks/ResolveDiskTest.php:24  (class-const-fetch)

MISSING: Avax\Filesystem\Disks\ResolveDisk  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Disks/ResolveDiskTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Disks/ResolveDiskTest.php:17  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Disks/ResolveDiskTest.php:27  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Disks/ResolveDiskTest.php:32  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Disks/ResolveDiskTest.php:39  (new)

MISSING: Avax\Filesystem\Files\CopyFile  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/CopyFileTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/CopyFileTest.php:39  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/CopyFileTest.php:49  (new)

MISSING: Avax\Filesystem\Files\DeleteFile  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/DeleteFileTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/DeleteFileTest.php:40  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/DeleteFileTest.php:49  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/DeleteFileTest.php:61  (new)

MISSING: Avax\Filesystem\Files\FileDeleteFailed  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/DeleteFileTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/DeleteFileTest.php:59  (class-const-fetch)

MISSING: Avax\Filesystem\Files\FileNotFound  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Disks/Local/LocalDiskTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Disks/Local/LocalDiskTest.php:49  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/ReadFileTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/ReadFileTest.php:42  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/ReadFileTest.php:54  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/MoveFileTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/MoveFileTest.php:48  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/CopyFileTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/CopyFileTest.php:47  (class-const-fetch)

MISSING: Avax\Filesystem\Files\MoveFile  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/MoveFileTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/MoveFileTest.php:39  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/MoveFileTest.php:50  (new)

MISSING: Avax\Filesystem\Files\ReadFile  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/ReadFileTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/ReadFileTest.php:35  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/ReadFileTest.php:45  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/ReadFileTest.php:60  (new)

MISSING: Avax\Filesystem\Files\WriteFile  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/WriteFileTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/WriteFileTest.php:32  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/WriteFileTest.php:43  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Files/WriteFileTest.php:55  (new)

MISSING: Avax\Filesystem\Paths\PathHasPermissions  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Paths/PathHasPermissionsTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Paths/PathHasPermissionsTest.php:28  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Paths/PathHasPermissionsTest.php:38  (new)
  - /home/shomsy/projects/avax/tests/Foundation/Filesystem/Paths/PathHasPermissionsTest.php:48  (new)

MISSING: Avax\Framework\System\Capabilities\ExternalState\System\Capabilities\ReadApiDescriptions\Memory  [CRITICAL]
  - /home/shomsy/projects/avax/framework/System/Capabilities/ExternalState/System/PublicSurface/ExternalState.php:7  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Capabilities/ExternalState/System/PublicSurface/ExternalState.php:37  (new)
  - /home/shomsy/projects/avax/framework/System/Capabilities/ExternalState/System/PublicSurface/ExternalState.php:57  (new)
  - /home/shomsy/projects/avax/framework/System/Capabilities/ExternalState/System/PublicSurface/ExternalState.php:77  (new)
  - /home/shomsy/projects/avax/framework/System/Capabilities/ExternalState/System/PublicSurface/ExternalState.php:97  (new)
  - /home/shomsy/projects/avax/framework/System/Capabilities/ExternalState/System/PublicSurface/ExternalState.php:126  (instanceof)

MISSING: Avax\Framework\System\Capabilities\ExternalState\System\Capabilities\ReadApiDescriptions\Redis  [CRITICAL]
  - /home/shomsy/projects/avax/framework/System/Capabilities/ExternalState/System/PublicSurface/ExternalState.php:8  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Capabilities/ExternalState/System/PublicSurface/ExternalState.php:34  (new)
  - /home/shomsy/projects/avax/framework/System/Capabilities/ExternalState/System/PublicSurface/ExternalState.php:54  (new)
  - /home/shomsy/projects/avax/framework/System/Capabilities/ExternalState/System/PublicSurface/ExternalState.php:74  (new)
  - /home/shomsy/projects/avax/framework/System/Capabilities/ExternalState/System/PublicSurface/ExternalState.php:94  (new)
  - /home/shomsy/projects/avax/framework/System/Capabilities/ExternalState/System/PublicSurface/ExternalState.php:122  (instanceof)

MISSING: Avax\Framework\System\Capabilities\PreCommit\Validators\BaseValidator  [CRITICAL]
  - /home/shomsy/projects/avax/framework/System/Capabilities/PreCommit/Validators/HowToRulesValidator.php:16  (extends)
  - /home/shomsy/projects/avax/framework/System/Capabilities/PreCommit/Validators/DeprecatedCodeValidator.php:14  (extends)
  - /home/shomsy/projects/avax/framework/System/Capabilities/PreCommit/Validators/PhpSyntaxValidator.php:15  (extends)
  - /home/shomsy/projects/avax/framework/System/Capabilities/PreCommit/Validators/ToolingIntegrationValidator.php:14  (extends)
  - /home/shomsy/projects/avax/framework/System/Capabilities/PreCommit/Validators/FileStructureValidator.php:21  (extends)
  - /home/shomsy/projects/avax/framework/System/Capabilities/PreCommit/Validators/LegacyCodeValidator.php:20  (extends)
  - /home/shomsy/projects/avax/framework/System/Capabilities/PreCommit/Validators/SecurityValidator.php:15  (extends)
  - /home/shomsy/projects/avax/framework/System/Capabilities/PreCommit/Validators/NamingConventionValidator.php:19  (extends)
  - /home/shomsy/projects/avax/framework/System/Capabilities/PreCommit/Validators/ScriptRunnerValidator.php:22  (extends)
  - /home/shomsy/projects/avax/framework/System/Capabilities/PreCommit/Validators/TodoCommentValidator.php:14  (extends)

MISSING: Avax\Framework\System\Capabilities\Runtime\Adapters\FrankenPhp\FrankenPhpRuntime  [MINOR]
  - /home/shomsy/projects/avax/tests/Contract/Runtime/WorkerRuntimeContractTest.php:7  (use-statement)
  - /home/shomsy/projects/avax/tests/Contract/Runtime/WorkerRuntimeContractTest.php:94  (class-const-fetch)

MISSING: Avax\Framework\System\Capabilities\Runtime\Adapters\RoadRunner\RoadRunnerRuntime  [MINOR]
  - /home/shomsy/projects/avax/tests/Contract/Runtime/WorkerRuntimeContractTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Contract/Runtime/WorkerRuntimeContractTest.php:95  (class-const-fetch)

MISSING: Avax\Framework\System\Capabilities\Runtime\Adapters\Swoole\SwooleRuntime  [MINOR]
  - /home/shomsy/projects/avax/tests/Contract/Runtime/WorkerRuntimeContractTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Contract/Runtime/WorkerRuntimeContractTest.php:96  (class-const-fetch)

MISSING: Avax\Framework\System\Capabilities\Runtime\Adapters\Workerman\WorkermanRuntime  [MINOR]
  - /home/shomsy/projects/avax/tests/Contract/Runtime/WorkerRuntimeContractTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Contract/Runtime/WorkerRuntimeContractTest.php:97  (class-const-fetch)

MISSING: Avax\Framework\System\Capabilities\Runtime\RunApplication\FrankenPhp\FrankenPhpRuntime  [CRITICAL]
  - /home/shomsy/projects/avax/framework/System/Configuration/ConfigureRuntime/RuntimeConfiguration.php:7  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Configuration/ConfigureRuntime/RuntimeConfiguration.php:65  (new)

MISSING: Avax\Framework\System\Capabilities\Runtime\RunApplication\RoadRunner\RoadRunnerRuntime  [CRITICAL]
  - /home/shomsy/projects/avax/framework/System/Configuration/ConfigureRuntime/RuntimeConfiguration.php:8  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Configuration/ConfigureRuntime/RuntimeConfiguration.php:64  (new)

MISSING: Avax\Framework\System\Capabilities\Runtime\RunApplication\Swoole\SwooleRuntime  [CRITICAL]
  - /home/shomsy/projects/avax/framework/System/Configuration/ConfigureRuntime/RuntimeConfiguration.php:9  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Configuration/ConfigureRuntime/RuntimeConfiguration.php:66  (new)

MISSING: Avax\Framework\System\Capabilities\Runtime\RunApplication\Workerman\WorkermanRuntime  [CRITICAL]
  - /home/shomsy/projects/avax/framework/System/Configuration/ConfigureRuntime/RuntimeConfiguration.php:10  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Configuration/ConfigureRuntime/RuntimeConfiguration.php:67  (new)

MISSING: Avax\Framework\System\PublicSurface\AppKernel  [MINOR]
  - /home/shomsy/projects/avax/components/compat.php:27  (use-statement)
  - /home/shomsy/projects/avax/components/compat.php:144  (class-const-fetch)

MISSING: Avax\Framework\System\PublicSurface\HttpKernel  [MINOR]
  - /home/shomsy/projects/avax/components/compat.php:29  (use-statement)
  - /home/shomsy/projects/avax/components/compat.php:143  (class-const-fetch)

MISSING: Avax\Framework\System\Runtime\RunApplication\Capabilities\RunApplicationOnRunApplicationOnPhpBuiltInServer  [MINOR]
  - /home/shomsy/projects/avax/framework/System/Runtime/RunApplication/PublicSurface/ServerResult.php:7  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Runtime/RunApplication/PublicSurface/Server.php:7  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Runtime/RunApplication/PublicSurface/Server.php:27  (static-call)

MISSING: Avax\HTTP\AppKernel  [MINOR]
  - /home/shomsy/projects/avax/tests/Integration/AppKernelIntegrationTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/AppKernelIntegrationTest.php:138  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Integration/AppKernelIntegrationTest.php:147  (static-call)

MISSING: Avax\HTTP\Dispatcher\ControllerDispatcher  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Integration/AppKernelIntegrationTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/AppKernelIntegrationTest.php:30  (property-type)
  - /home/shomsy/projects/avax/tests/Integration/AppKernelIntegrationTest.php:175  (new)

MISSING: Avax\HTTP\Middleware\CsrfVerificationMiddleware  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Integration/AppKernelIntegrationTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/AppKernelIntegrationTest.php:69  (new)
  - /home/shomsy/projects/avax/tests/Integration/AppKernelIntegrationTest.php:99  (new)
  - /home/shomsy/projects/avax/tests/Integration/AppKernelIntegrationTest.php:121  (new)

MISSING: Avax\HTTP\ResolveRouteFromHttpRequest  [MINOR]
  - /home/shomsy/projects/avax/components/compat.php:30  (use-statement)
  - /home/shomsy/projects/avax/components/compat.php:145  (class-const-fetch)

MISSING: Avax\HTTP\Response\Response  [MINOR]
  - /home/shomsy/projects/avax/examples/minimal-http-app/app/HTTP/routes/web.routes.php:6  (use-statement)
  - /home/shomsy/projects/avax/examples/minimal-http-app/app/HTTP/routes/web.routes.php:30  (static-call)
  - /home/shomsy/projects/avax/examples/minimal-http-app/app/HTTP/routes/web.routes.php:36  (static-call)

MISSING: Avax\HTTP\RouterBootstrapper  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Integration/AppKernelIntegrationTest.php:13  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/AppKernelIntegrationTest.php:34  (property-type)
  - /home/shomsy/projects/avax/tests/Integration/AppKernelIntegrationTest.php:181  (new)

MISSING: Avax\HTTP\Router\RouterInterface  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Integration/AppKernelIntegrationTest.php:11  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/AppKernelIntegrationTest.php:28  (property-type)
  - /home/shomsy/projects/avax/tests/Integration/AppKernelIntegrationTest.php:173  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Flows/RegisterBindings/RegisterBindingsTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Flows/RegisterBindings/RegisterBindingsTest.php:81  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Flows/RegisterBindings/RegisterBindingsTest.php:98  (implements)

MISSING: Avax\HTTP\Router\System\Capabilities\RouteDefinition\RouteCollection  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Integration/AppKernelIntegrationTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/AppKernelIntegrationTest.php:174  (new)

MISSING: Avax\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/HttpApplicationTest.php:14  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Providers/Runtime/Http/HttpApplicationTest.php:95  (return-type)

MISSING: Avax\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouteBuilder  [MINOR]
  - /home/shomsy/projects/avax/examples/golden-path-app/routes/web.php:5  (use-statement)
  - /home/shomsy/projects/avax/routes/web.php:7  (use-statement)

MISSING: Avax\Integration\ObjectStorage\System\Capabilities\Ports\ObjectStoragePort  [CRITICAL]
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsOnS3.php:7  (use-statement)
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsOnS3.php:14  (implements)
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsInMemory.php:7  (use-statement)
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsInMemory.php:10  (implements)
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsOnLocalFilesystem.php:7  (use-statement)
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsOnLocalFilesystem.php:10  (implements)
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/Health/ObjectStorageHealthReport.php:7  (use-statement)
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/Health/CheckObjectStorageHealth.php:7  (use-statement)
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/Health/CheckObjectStorageHealth.php:13  (constructor-param)

MISSING: Avax\Integration\ObjectStorage\System\Capabilities\Ports\ObjectStorageResult  [MINOR]
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsOnS3.php:8  (use-statement)
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsOnS3.php:33  (return-type)
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsOnS3.php:43  (static-call)
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsOnS3.php:45  (static-call)
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsInMemory.php:8  (use-statement)
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsInMemory.php:17  (return-type)
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsInMemory.php:21  (static-call)
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsOnLocalFilesystem.php:8  (use-statement)
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsOnLocalFilesystem.php:22  (return-type)
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsOnLocalFilesystem.php:34  (static-call)
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsOnLocalFilesystem.php:37  (static-call)

MISSING: Avax\Integration\ObjectStorage\System\Foundation\Failure\ObjectStorageUnavailable  [MINOR]
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsOnS3.php:9  (use-statement)

MISSING: Avax\Logging\LoggerFactory  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Container/var/cache/Avax_Logging_LoggerFactory.php:6  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Container/var/cache/Avax_Logging_LoggerFactory.php:9  (class-const-fetch)

MISSING: Avax\Operations\RuntimeSupervision\System\Capabilities\Health\SupervisorHealthReport  [MINOR]
  - /home/shomsy/projects/avax/components/Operations/RuntimeSupervision/System/PublicSurface/RuntimeSupervision.php:9  (use-statement)

MISSING: Avax\Tests\Foundation\Container\Capabilities\Resolution\Kernel\ContainerKernel  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:36  (property-type)
  - /home/shomsy/projects/avax/tests/Foundation/Container/Capabilities/Resolution/Kernel/ContainerKernelTest.php:98  (new)

MISSING: Avax\Tests\Foundation\Database\Unit\Attributes\Column  [MINOR]
  - /home/shomsy/projects/avax/components/compat.php:124  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Database/Unit/CriticalPathTest.php:142  (attribute)
  - /home/shomsy/projects/avax/tests/Foundation/Database/Unit/CriticalPathTest.php:145  (attribute)
  - /home/shomsy/projects/avax/tests/Foundation/Database/Unit/CriticalPathTest.php:148  (attribute)

MISSING: Avax\Tests\Foundation\HTTP\Router\Unit\InvalidArgumentException  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteValidationCentralizationTest.php:45  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteValidationCentralizationTest.php:63  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteValidationCentralizationTest.php:94  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteValidationCentralizationTest.php:107  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteValidationCentralizationTest.php:141  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteValidationCentralizationTest.php:172  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:60  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:71  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:82  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:93  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:104  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:115  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:126  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:137  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RoutePathValidatorTest.php:148  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslIntegrationTest.php:113  (class-const-fetch)

MISSING: Avax\Tests\Foundation\HTTP\Router\Unit\Override  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterBootstrapConcurrencyTest.php:108  (attribute)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RegexConstraintTest.php:219  (attribute)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterContractTest.php:171  (attribute)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteExportValidatorTest.php:270  (attribute)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteGroupStackTest.php:150  (attribute)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterDslAnyTest.php:121  (attribute)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/DomainAwareMatcherTest.php:250  (attribute)

MISSING: Avax\Tests\Foundation\HTTP\Router\Unit\RouteCollector  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteBootstrapperTest.php:75  (class-const-fetch)

MISSING: Avax\Tests\Foundation\HTTP\Router\Unit\RuntimeException  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouterBootstrapConcurrencyTest.php:38  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RegexConstraintTest.php:64  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Router/Unit/RouteGroupStackTest.php:117  (new)

MISSING: Avax\Tests\Integration\InvalidArgumentException  [MINOR]
  - /home/shomsy/projects/avax/tests/Integration/AppKernelIntegrationTest.php:162  (class-const-fetch)

MISSING: Avax\Tests\Integration\Override  [MINOR]
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:247  (attribute)
  - /home/shomsy/projects/avax/tests/Integration/AppKernelIntegrationTest.php:168  (attribute)
  - /home/shomsy/projects/avax/tests/Integration/RouterIntegrationTest.php:215  (attribute)

MISSING: Avax\Tests\Integration\Throwable  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:116  (catch)
  - /home/shomsy/projects/avax/tests/Integration/RouterHardeningTest.php:118  (class-const-fetch)

MISSING: Avax\Tests\Unit\Components\Application\Container\System\PublicSurface\DIContainerInterface  [MINOR]
  - /home/shomsy/projects/avax/tests/Unit/Components/Application/Container/System/PublicSurface/ContainerInterfaceTest.php:153  (class-const-fetch)

MISSING: Avax\Tests\Unit\Framework\System\Capabilities\Runtime\UsesClass  [MINOR]
  - /home/shomsy/projects/avax/tests/Unit/Framework/System/Capabilities/Runtime/RuntimeResultTest.php:14  (attribute)

MISSING: Avax\Text\Pattern  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Text/RegexExceptionTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Text/RegexExceptionTest.php:18  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Text/RegexExceptionTest.php:25  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Text/RegexExceptionTest.php:31  (static-call)

MISSING: Avax\Text\RegexException  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/Text/TextReplaceRegexCallbackTest.php:8  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Text/TextReplaceRegexCallbackTest.php:33  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Text/RegexExceptionTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Text/RegexExceptionTest.php:15  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Text/RegexExceptionTest.php:23  (class-const-fetch)
  - /home/shomsy/projects/avax/tests/Foundation/Text/RegexExceptionTest.php:33  (catch)

MISSING: Avax\Text\Text  [MINOR]
  - /home/shomsy/projects/avax/tests/Foundation/Text/TextReplaceRegexCallbackTest.php:9  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/Text/TextReplaceRegexCallbackTest.php:15  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Text/TextReplaceRegexCallbackTest.php:24  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Text/TextReplaceRegexCallbackTest.php:35  (static-call)
  - /home/shomsy/projects/avax/tests/Foundation/Text/TextReplaceRegexCallbackTest.php:42  (static-call)

MISSING: Avax\Tooling\Architecture\DirectoryIterator  [CRITICAL]
  - /home/shomsy/projects/avax/tooling/Architecture/check-docs-mirror.php:21  (new)

MISSING: Avax\Tooling\Architecture\FilesystemIterator  [MINOR]
  - /home/shomsy/projects/avax/tooling/Architecture/check-runtime-leaks.php:30  (class-const-fetch)

MISSING: Avax\Tooling\Architecture\RecursiveDirectoryIterator  [CRITICAL]
  - /home/shomsy/projects/avax/tooling/Architecture/check-duplicate-owners.php:18  (new)
  - /home/shomsy/projects/avax/tooling/Architecture/check-namespace-drift.php:18  (new)
  - /home/shomsy/projects/avax/tooling/Architecture/check-forbidden-folders.php:39  (new)
  - /home/shomsy/projects/avax/tooling/Architecture/check-public-surface.php:18  (new)
  - /home/shomsy/projects/avax/tooling/Architecture/check-runtime-leaks.php:30  (new)

MISSING: Avax\Tooling\Architecture\RecursiveIteratorIterator  [CRITICAL]
  - /home/shomsy/projects/avax/tooling/Architecture/check-duplicate-owners.php:18  (new)
  - /home/shomsy/projects/avax/tooling/Architecture/check-namespace-drift.php:18  (new)
  - /home/shomsy/projects/avax/tooling/Architecture/check-forbidden-folders.php:39  (new)
  - /home/shomsy/projects/avax/tooling/Architecture/check-public-surface.php:18  (new)
  - /home/shomsy/projects/avax/tooling/Architecture/check-runtime-leaks.php:29  (new)

MISSING: Avax\Tooling\Architecture\SplFileInfo  [MINOR]
  - /home/shomsy/projects/avax/tooling/Architecture/check-runtime-leaks.php:34  (instanceof)

MISSING: Avax\Tooling\DependencyMap\Capabilities\Graph\DependencyGraph  [CRITICAL]
  - /home/shomsy/projects/avax/tooling/DependencyMap/System/PublicSurface/DependencyMap.php:7  (use-statement)
  - /home/shomsy/projects/avax/tooling/DependencyMap/System/PublicSurface/DependencyMap.php:11  (constructor-param)

MISSING: Avax\Tooling\Exception  [CRITICAL]
  - /home/shomsy/projects/avax/tooling/RefVisitor.php:83  (catch)
  - /home/shomsy/projects/avax/tooling/RefVisitor.php:422  (catch)

MISSING: Avax\Tooling\FilesystemIterator  [MINOR]
  - /home/shomsy/projects/avax/tooling/generate-class-map.php:21  (class-const-fetch)

MISSING: Avax\Tooling\PreCommit\RuntimeException  [CRITICAL]
  - /home/shomsy/projects/avax/tooling/PreCommit/HookInstallerException.php:6  (extends)

MISSING: Avax\Tooling\RecursiveDirectoryIterator  [CRITICAL]
  - /home/shomsy/projects/avax/tooling/RefVisitor.php:40  (new)
  - /home/shomsy/projects/avax/tooling/generate-class-map.php:21  (new)

MISSING: Avax\Tooling\RecursiveIteratorIterator  [CRITICAL]
  - /home/shomsy/projects/avax/tooling/RefVisitor.php:40  (new)
  - /home/shomsy/projects/avax/tooling/generate-class-map.php:20  (new)
  - /home/shomsy/projects/avax/tooling/generate-class-map.php:22  (class-const-fetch)

MISSING: Avax\Tooling\Refactor\FilesystemIterator  [MINOR]
  - /home/shomsy/projects/avax/tooling/Refactor/FreezeComponentTaxonomy.php:728  (class-const-fetch)
  - /home/shomsy/projects/avax/tooling/Refactor/FreezeRecoveredComponentsTaxonomy.php:476  (class-const-fetch)
  - /home/shomsy/projects/avax/tooling/Refactor/RepairTestLayer.php:182  (class-const-fetch)

MISSING: Avax\Tooling\Refactor\RecursiveDirectoryIterator  [CRITICAL]
  - /home/shomsy/projects/avax/tooling/Refactor/FreezeComponentTaxonomy.php:728  (new)
  - /home/shomsy/projects/avax/tooling/Refactor/fix-namespaces-remove-system.php:19  (new)
  - /home/shomsy/projects/avax/tooling/Refactor/fix-moved-files-namespaces.php:26  (new)
  - /home/shomsy/projects/avax/tooling/Refactor/fix-all-namespaces.php:113  (new)
  - /home/shomsy/projects/avax/tooling/Refactor/check-component-gates.php:65  (new)
  - /home/shomsy/projects/avax/tooling/Refactor/NamespaceReconstructor.php:25  (new)
  - /home/shomsy/projects/avax/tooling/Refactor/NamespaceReconstructor.php:62  (new)
  - /home/shomsy/projects/avax/tooling/Refactor/FreezeRecoveredComponentsTaxonomy.php:476  (new)
  - /home/shomsy/projects/avax/tooling/Refactor/RepairTestLayer.php:182  (new)

MISSING: Avax\Tooling\Refactor\RecursiveIteratorIterator  [CRITICAL]
  - /home/shomsy/projects/avax/tooling/Refactor/FreezeComponentTaxonomy.php:727  (new)
  - /home/shomsy/projects/avax/tooling/Refactor/fix-namespaces-remove-system.php:18  (new)
  - /home/shomsy/projects/avax/tooling/Refactor/fix-moved-files-namespaces.php:25  (new)
  - /home/shomsy/projects/avax/tooling/Refactor/fix-all-namespaces.php:112  (new)
  - /home/shomsy/projects/avax/tooling/Refactor/fix-all-namespaces.php:114  (class-const-fetch)
  - /home/shomsy/projects/avax/tooling/Refactor/check-component-gates.php:64  (new)
  - /home/shomsy/projects/avax/tooling/Refactor/NamespaceReconstructor.php:25  (new)
  - /home/shomsy/projects/avax/tooling/Refactor/NamespaceReconstructor.php:62  (new)
  - /home/shomsy/projects/avax/tooling/Refactor/FreezeRecoveredComponentsTaxonomy.php:475  (new)
  - /home/shomsy/projects/avax/tooling/Refactor/RepairTestLayer.php:181  (new)

MISSING: Avax\Tooling\Refactor\RuntimeException  [CRITICAL]
  - /home/shomsy/projects/avax/tooling/Refactor/FreezeComponentTaxonomy.php:29  (new)
  - /home/shomsy/projects/avax/tooling/Refactor/FreezeComponentTaxonomy.php:398  (new)
  - /home/shomsy/projects/avax/tooling/Refactor/FreezeComponentTaxonomy.php:654  (new)
  - /home/shomsy/projects/avax/tooling/Refactor/FreezeRecoveredComponentsTaxonomy.php:29  (new)
  - /home/shomsy/projects/avax/tooling/Refactor/FreezeRecoveredComponentsTaxonomy.php:164  (new)
  - /home/shomsy/projects/avax/tooling/Refactor/FreezeRecoveredComponentsTaxonomy.php:402  (new)
  - /home/shomsy/projects/avax/tooling/Refactor/RepairTestLayer.php:40  (new)
  - /home/shomsy/projects/avax/tooling/Refactor/RepairTestLayer.php:112  (new)

MISSING: Avax\Tooling\Refactor\SplFileInfo  [MINOR]
  - /home/shomsy/projects/avax/tooling/Refactor/FreezeComponentTaxonomy.php:732  (instanceof)
  - /home/shomsy/projects/avax/tooling/Refactor/FreezeRecoveredComponentsTaxonomy.php:480  (instanceof)
  - /home/shomsy/projects/avax/tooling/Refactor/RepairTestLayer.php:186  (instanceof)

MISSING: Avax\Tooling\Refactor\Throwable  [CRITICAL]
  - /home/shomsy/projects/avax/tooling/Refactor/FreezeComponentTaxonomy.php:741  (catch)
  - /home/shomsy/projects/avax/tooling/Refactor/FreezeRecoveredComponentsTaxonomy.php:535  (catch)
  - /home/shomsy/projects/avax/tooling/Refactor/RepairTestLayer.php:293  (catch)

MISSING: Avax\Tooling\SplFileInfo  [MINOR]
  - /home/shomsy/projects/avax/tooling/RefVisitor.php:41  (instanceof)

MISSING: Avax\\Components\\Application\\Cache\\System\\PublicSurface\\Cache  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Application\\Container\\System\\Container  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Config\\System\\Capabilities\\Architecture\\AppPath  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\DataStack\\Database\\Database  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\DataStack\\Database\\EntityManager  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\DataStack\\Database\\Migrations  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\DataStack\\Database\\Query  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\DataStack\\Database\\Schema  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\DataStack\\Database\\Telemetry  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\DataStack\\Database\\Transactions  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Arrhae  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Collection  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\DataList\\DataList  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Composites\\MapEntry  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Composites\\Pair  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Composites\\Record  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Composites\\RecordField  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Composites\\Tuple2  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Composites\\Tuple3  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Composites\\Tuple4  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Values\\Option\\None  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Values\\Option\\Option  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Values\\Option\\Some  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Values\\Result\\Failure  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Values\\Result\\Result  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Internal\\Values\\Result\\Success  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Map\\Map  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Operators\\Aggregate\\AverageValues  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Operators\\Aggregate\\SumValues  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Operators\\Search\\ContainsValue  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Operators\\Search\\MatchTextFuzzily  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Operators\\Search\\MatchTextPartially  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Operators\\Search\\SearchValue  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Operators\\Transform\\FilterValues  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Operators\\Transform\\MapValues  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\Collections\\Set\\Set  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\DataShape\\DataField  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\DataShape\\DataFieldType  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\DataShape\\InspectDataShape  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\ObjectReading\\NormalizeDataObjectValue  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Capabilities\\ObjectReading\\ReadDataObject  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Flows\\SerializeDataObject\\ConvertDataObjectToArray  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Flows\\SerializeDataObject\\ConvertDataObjectToJson  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Flows\\SerializeDataObject\\ConvertDataObjectToJsonApi  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Flows\\SerializeDataObject\\ConvertDataObjectToStdClass  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Data\\System\\Flows\\SerializeDataObject\\SerializeDataObject  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\DumpDebugger\\System\\PublicSurface\\Dump  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\HTTP\\Request\\Request  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\HTTP\\System\\Configuration\\RouterBootstrapper  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Logging\\System\\Configuration\\RegisterLogging  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Request\\System\\Capabilities\\RequestAttributes\\RequestAttributes  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Request\\System\\Capabilities\\RequestBody\\ParsedBody  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Request\\System\\Capabilities\\RequestBody\\RequestBody  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Request\\System\\Capabilities\\RequestCookies\\RequestCookies  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Request\\System\\Capabilities\\RequestHeaders\\RequestHeaders  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Request\\System\\Capabilities\\RequestInit  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Request\\System\\Capabilities\\RequestTarget\\ReadRequestTarget  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Request\\System\\Capabilities\\RequestedInputs\\RequestedInputs  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Request\\System\\Capabilities\\ServerInit  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Request\\System\\Capabilities\\UploadedFiles\\UploadedFiles  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Request\\System\\PublicSurface\\ServerRequest  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Response\\System\\Capabilities\\Body\\ResponseBody  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Response\\System\\Capabilities\\Caching\\CacheControl  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Response\\System\\Capabilities\\Caching\\Etag  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Response\\System\\Capabilities\\Caching\\LastModified  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Response\\System\\Capabilities\\Headers\\ResponseHeaders  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Response\\System\\Capabilities\\Message\\ResponseMessage  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Response\\System\\Capabilities\\ResponseEmitter  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Response\\System\\Flows\\BuildResponse\\BuildResponse  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Response\\System\\Flows\\EmitResponse\\EmitResponse  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Response\\System\\PublicSurface\\Response  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\Components\\Text\\System\\PublicSurface\\Text  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\DataFoundation\\DataTransfer\\Capabilities\\Attributes\\CastWith  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\DataFoundation\\DataTransfer\\Capabilities\\Attributes\\Hidden  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\DataFoundation\\DataTransfer\\Capabilities\\Attributes\\ListOf  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\DataFoundation\\DataTransfer\\Capabilities\\Attributes\\MapFrom  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\DataFoundation\\DataTransfer\\DataTransfer  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\DataFoundation\\ObjectHandling\\DTO\\DTOValidationException  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\DataFoundation\\Validation\\Attributes\\Rules\\EmailRule  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\DataFoundation\\Validation\\Attributes\\Rules\\MinLengthRule  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Avax\\HTTP\\Request\\RequestDtoFactory  [CRITICAL]
  - /home/shomsy/projects/avax/components/compat.php:0  (class_alias target)

MISSING: Aws\PresignUrlMiddleware  [CRITICAL]
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsOnS3.php:10  (use-statement)
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsOnS3.php:115  (new)

MISSING: Aws\S3\S3Client  [CRITICAL]
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsOnS3.php:11  (use-statement)
  - /home/shomsy/projects/avax/labs/Integration/ObjectStorage/System/Capabilities/StoreObjects/StoreObjectsOnS3.php:134  (new)

MISSING: BenchSharedService  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:126  (constructor-param)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:147  (property-type)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:149  (property-type)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:152  (param-type)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:276  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:277  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:285  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:286  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:294  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:295  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:296  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:307  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:310  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:410  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:419  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:428  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:440  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:443  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:463  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:464  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:467  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:481  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:482  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:485  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:500  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:501  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/benchmarks/run.php:504  (class-const-fetch)

MISSING: BlueprintTarget  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Capabilities/Declaration/Blueprints/CreateDependencyBlueprintSmokeTest.php:39  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Capabilities/Declaration/Blueprints/CreateDependencyBlueprintSmokeTest.php:40  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Capabilities/Declaration/Blueprints/CreateDependencyBlueprintSmokeTest.php:44  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Capabilities/Declaration/Blueprints/CreateDependencyBlueprintSmokeTest.php:57  (class-const-fetch)

MISSING: CallArgumentGreeter  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Capabilities/Execution/Injection/Invocation/ResolveCallArgumentsSmokeTest.php:34  (class-const-fetch)

MISSING: CallGreeter  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CallFunction/CallFunctionSmokeTest.php:44  (class-const-fetch)

MISSING: CloseScopedService  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CloseScope/CloseScopeSmokeTest.php:12  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CloseScope/CloseScopeSmokeTest.php:15  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CloseScope/CloseScopeSmokeTest.php:19  (class-const-fetch)

MISSING: CompatibilityDependency  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompiledCompatibilitySmokeTest.php:19  (constructor-param)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompiledCompatibilitySmokeTest.php:33  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompiledCompatibilitySmokeTest.php:34  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompiledCompatibilitySmokeTest.php:44  (class-const-fetch)

MISSING: CompileReportDependency  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompileReportSmokeTest.php:11  (return-type)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompileReportSmokeTest.php:32  (constructor-param)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompileReportSmokeTest.php:36  (return-type)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompileReportSmokeTest.php:55  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompileReportSmokeTest.php:56  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompileReportSmokeTest.php:57  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompileReportSmokeTest.php:90  (class-const-fetch)

MISSING: CompiledCacheDependency  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompiledCacheSmokeTest.php:19  (constructor-param)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompiledCacheSmokeTest.php:30  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompiledCacheSmokeTest.php:38  (class-const-fetch)

MISSING: CompiledGreeter  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompiledContainerSmokeTest.php:50  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompiledContainerSmokeTest.php:70  (class-const-fetch)

MISSING: CreateGreeter  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CreateContainerSmokeTest.php:33  (class-const-fetch)

MISSING: Cron\CronExpression  [CRITICAL]
  - /home/shomsy/projects/avax/components/Operations/Scheduler/System/Capabilities/Cron/CronExpression.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Operations/Scheduler/System/Capabilities/Cron/CronExpression.php:14  (new)
  - /home/shomsy/projects/avax/components/Operations/Scheduler/System/Capabilities/Cron/CronExpression.php:21  (new)
  - /home/shomsy/projects/avax/components/Operations/Scheduler/System/Capabilities/Cron/CronExpression.php:28  (static-call)

MISSING: DecoratedService  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/ResolveService/DecoratorAndRuntimeInputSmokeTest.php:32  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/ResolveService/DecoratorAndRuntimeInputSmokeTest.php:79  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/ResolveService/DecoratorAndRuntimeInputSmokeTest.php:115  (instanceof)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/ResolveService/DecoratorAndRuntimeInputSmokeTest.php:173  (class-const-fetch)

MISSING: DefaultRegisterLogger  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/RegisterServices/RegisterDependenciesSmokeTest.php:63  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/RegisterServices/RegisterDependenciesSmokeTest.php:66  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/RegisterServices/RegisterDependenciesSmokeTest.php:68  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/RegisterServices/RegisterDependenciesSmokeTest.php:113  (class-const-fetch)

MISSING: DeferredRegularService  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/RegisterServices/DeferredDependenciesSmokeTest.php:25  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/RegisterServices/DeferredDependenciesSmokeTest.php:29  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/RegisterServices/DeferredDependenciesSmokeTest.php:31  (class-const-fetch)

MISSING: DiagnosticsService  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/ResolveService/ContextAndDiagnosticsSmokeTest.php:48  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/ResolveService/ContextAndDiagnosticsSmokeTest.php:77  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/ResolveService/ContextAndDiagnosticsSmokeTest.php:164  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/ResolveService/ContextAndDiagnosticsSmokeTest.php:174  (class-const-fetch)

MISSING: ExecutionModeDependency  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/ExecutionModeSmokeTest.php:15  (constructor-param)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/ExecutionModeSmokeTest.php:26  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/ExecutionModeSmokeTest.php:60  (class-const-fetch)

MISSING: ExportedSliceGateway  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/ResolveService/SliceViewSmokeTest.php:45  (class-const-fetch)

MISSING: Firebase\JWT\JWT  [MINOR]
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/Signing/JwtSigner.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/Signing/JwtSigner.php:17  (static-call)
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/Verification/TokenVerifier.php:9  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/Verification/TokenVerifier.php:29  (static-call)
  - /home/shomsy/projects/avax/components/HTTP/Security/System/Capabilities/SignedUrls/SignedUrlGenerator.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Security/System/Capabilities/SignedUrls/SignedUrlGenerator.php:32  (static-call)
  - /home/shomsy/projects/avax/components/HTTP/Security/System/Capabilities/SignedUrls/SignedUrlVerifier.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Security/System/Capabilities/SignedUrls/SignedUrlVerifier.php:24  (static-call)

MISSING: Firebase\JWT\Key  [CRITICAL]
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/Verification/TokenVerifier.php:10  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Tokens/System/Capabilities/JwtAuth/Verification/TokenVerifier.php:31  (new)
  - /home/shomsy/projects/avax/components/HTTP/Security/System/Capabilities/SignedUrls/SignedUrlVerifier.php:9  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Security/System/Capabilities/SignedUrls/SignedUrlVerifier.php:26  (new)

MISSING: FreshnessDependencyV1  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompiledFreshnessSmokeTest.php:48  (class-const-fetch)

MISSING: GeneratedFixtureDependency  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Container/tests/fixtures/generated_runtime_fixture.php:18  (constructor-param)
  - /home/shomsy/projects/avax/components/Application/Container/tests/fixtures/generated_runtime_fixture.php:32  (class-const-fetch)

MISSING: GraphToolIdentityService  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Container/tests/fixtures/graph_tool_fixture.php:19  (constructor-param)
  - /home/shomsy/projects/avax/components/Application/Container/tests/fixtures/graph_tool_fixture.php:39  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/fixtures/graph_tool_fixture.php:69  (class-const-fetch)

MISSING: GuzzleHttp\Psr7\Request  [CRITICAL]
  - /home/shomsy/projects/avax/components/HTTP/Client/System/Capabilities/Transports/HttpTransportInterface.php:10  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Client/System/Capabilities/Transports/HttpTransportInterface.php:37  (return-type)
  - /home/shomsy/projects/avax/components/HTTP/Client/System/Capabilities/Transports/CurlTransport.php:13  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Client/System/Capabilities/Transports/CurlTransport.php:288  (return-type)
  - /home/shomsy/projects/avax/components/HTTP/Client/System/Capabilities/Transports/CurlTransport.php:292  (new)
  - /home/shomsy/projects/avax/components/HTTP/Client/System/Flows/SendHttpRequest/BuildOutboundRequest.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Client/System/Flows/SendHttpRequest/BuildOutboundRequest.php:26  (return-type)
  - /home/shomsy/projects/avax/components/HTTP/Client/System/Flows/SendHttpRequest/BuildOutboundRequest.php:31  (new)

MISSING: GuzzleHttp\Psr7\Response  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/RateLimiterUnitTest.php:12  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/RateLimiterUnitTest.php:89  (new)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/RateLimiterUnitTest.php:104  (new)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/RateLimiterUnitTest.php:105  (new)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/RateLimiterUnitTest.php:120  (new)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/RateLimiterUnitTest.php:121  (new)

MISSING: GuzzleHttp\Psr7\ServerRequest  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/RateLimiterUnitTest.php:13  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/RateLimiterUnitTest.php:88  (new)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/RateLimiterUnitTest.php:102  (new)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/RateLimiterUnitTest.php:117  (new)
  - /home/shomsy/projects/avax/tests/Unit/Components/Enterprise/RateLimiterUnitTest.php:118  (new)
  - /home/shomsy/projects/avax/tests/Unit/Components/HTTP/ApiVersioning/ApiVersionTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Unit/Components/HTTP/ApiVersioning/ApiVersionTest.php:20  (new)

MISSING: GuzzleHttp\Psr7\UploadedFile  [CRITICAL]
  - /home/shomsy/projects/avax/components/HTTP/Request/ServerRequest/IncomingRequest/ServerRequest.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Request/ServerRequest/IncomingRequest/ServerRequest.php:222  (new)

MISSING: GuzzleHttp\Psr7\Uri  [CRITICAL]
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:29  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:57  (new)

MISSING: GuzzleHttp\Psr7\Utils  [MINOR]
  - /home/shomsy/projects/avax/components/HTTP/Request/System/Capabilities/Files/UploadedFile.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Request/System/Capabilities/Files/UploadedFile.php:24  (static-call)
  - /home/shomsy/projects/avax/components/HTTP/Request/System/PublicSurface/Request.php:12  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Request/System/PublicSurface/Request.php:100  (static-call)
  - /home/shomsy/projects/avax/components/HTTP/Request/ServerRequest/IncomingRequest/ServerRequest.php:9  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Request/ServerRequest/IncomingRequest/ServerRequest.php:49  (static-call)
  - /home/shomsy/projects/avax/components/HTTP/Request/ServerRequest/IncomingRequest/ServerRequest.php:195  (static-call)
  - /home/shomsy/projects/avax/components/HTTP/Request/ServerRequest/IncomingRequest/ServerRequest.php:214  (static-call)
  - /home/shomsy/projects/avax/components/HTTP/Request/ServerRequest/IncomingRequest/ServerRequest.php:300  (static-call)
  - /home/shomsy/projects/avax/components/HTTP/Response/ResponseFactory.php:9  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Response/ResponseFactory.php:21  (static-call)
  - /home/shomsy/projects/avax/components/HTTP/Response/ResponseFactory.php:31  (static-call)
  - /home/shomsy/projects/avax/components/HTTP/Response/ResponseFactory.php:39  (static-call)
  - /home/shomsy/projects/avax/components/HTTP/Response/ResponseFactory.php:62  (static-call)
  - /home/shomsy/projects/avax/components/HTTP/Response/System/Capabilities/Streaming/StreamResponseBody.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Response/System/Capabilities/Streaming/StreamResponseBody.php:20  (static-call)
  - /home/shomsy/projects/avax/components/HTTP/Response/System/Flows/BuildResponse/NormalizeResponseBody.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Response/System/Flows/BuildResponse/NormalizeResponseBody.php:14  (static-call)
  - /home/shomsy/projects/avax/components/HTTP/Response/System/Configuration/ResponseBuilder.php:9  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Response/System/Configuration/ResponseBuilder.php:121  (static-call)
  - /home/shomsy/projects/avax/components/HTTP/Response/System/PublicSurface/Response.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Response/System/PublicSurface/Response.php:18  (static-call)

MISSING: HintIdentityService  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Container/tests/fixtures/analysis_hints_fixture.php:23  (constructor-param)
  - /home/shomsy/projects/avax/components/Application/Container/tests/fixtures/analysis_hints_fixture.php:35  (class-const-fetch)

MISSING: InlineSmokeCompiled  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Capabilities/Runtime/HotPathInlinerSmokeTest.php:28  (new)

MISSING: IntegrityDependency  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompiledIntegritySmokeTest.php:22  (constructor-param)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompiledIntegritySmokeTest.php:39  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompiledIntegritySmokeTest.php:40  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompiledIntegritySmokeTest.php:52  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompiledIntegritySmokeTest.php:90  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompiledIntegritySmokeTest.php:91  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompiledIntegritySmokeTest.php:95  (class-const-fetch)

MISSING: Jenssegers\Blade\Blade  [CRITICAL]
  - /home/shomsy/projects/avax/components/Presentation/View/System/Capabilities/Engines/BladeTemplateEngine.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Presentation/View/System/Capabilities/Engines/BladeTemplateEngine.php:10  (extends)
  - /home/shomsy/projects/avax/components/Presentation/View/BladeTemplateEngine.php:10  (use-statement)
  - /home/shomsy/projects/avax/components/Presentation/View/BladeTemplateEngine.php:13  (extends)

MISSING: LazyCounter  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/ResolveService/LazyProxySmokeTest.php:18  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/ResolveService/LazyProxySmokeTest.php:20  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/ResolveService/LazyProxySmokeTest.php:22  (class-const-fetch)

MISSING: LifecycleService  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/ContainerLifecycleSmokeTest.php:36  (class-const-fetch)

MISSING: Memcached  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Cache/System/Capabilities/Stores/MemcachedCacheStore.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Cache/System/Capabilities/Stores/MemcachedCacheStore.php:13  (property-type)
  - /home/shomsy/projects/avax/components/Application/Cache/System/Capabilities/Stores/MemcachedCacheStore.php:19  (new)
  - /home/shomsy/projects/avax/components/Application/Cache/System/Capabilities/Stores/MemcachedCacheStore.php:62  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Cache/System/Capabilities/Stores/MemcachedCacheStore.php:70  (class-const-fetch)

MISSING: Nyholm\Psr7\Factory\Psr17Factory  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Integration/AppKernelIntegrationTest.php:16  (use-statement)
  - /home/shomsy/projects/avax/tests/Integration/AppKernelIntegrationTest.php:171  (new)

MISSING: Nyholm\Psr7\ServerRequest  [CRITICAL]
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Context/HttpContextTest.php:10  (use-statement)
  - /home/shomsy/projects/avax/tests/Foundation/HTTP/Context/HttpContextTest.php:16  (new)

MISSING: OpenScopedService  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/OpenScope/OpenScopeSmokeTest.php:10  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/OpenScope/OpenScopeSmokeTest.php:13  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/OpenScope/OpenScopeSmokeTest.php:14  (class-const-fetch)

MISSING: OrderingProviderAlpha  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/DeterministicOrderingSmokeTest.php:25  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/DeterministicOrderingSmokeTest.php:33  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/DeterministicOrderingSmokeTest.php:97  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/DeterministicOrderingSmokeTest.php:97  (new)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/DeterministicOrderingSmokeTest.php:102  (class-const-fetch)

MISSING: PhpCsFixer\Config  [CRITICAL]
  - /home/shomsy/projects/avax/.php-cs-fixer.dist.php:14  (new)

MISSING: PhpCsFixer\Finder  [MINOR]
  - /home/shomsy/projects/avax/.php-cs-fixer.dist.php:3  (static-call)

MISSING: PolicyDependencyA  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/ResolveService/PolicyAndStructureDiffSmokeTest.php:49  (class-const-fetch)

MISSING: PoolBucketService  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Capabilities/Runtime/DependencyPoolSmokeTest.php:39  (new)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Capabilities/Runtime/DependencyPoolSmokeTest.php:48  (class-const-fetch)

MISSING: Presentation\HTTP\Middleware\OfficeIpRestrictionMiddleware  [MINOR]
  - /home/shomsy/projects/avax/examples/minimal-http-app/config/middleware.php:20  (use-statement)
  - /home/shomsy/projects/avax/examples/minimal-http-app/config/middleware.php:37  (class-const-fetch)

MISSING: ProviderState  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/BootProviders/BootProvidersSmokeTest.php:43  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/BootProviders/BootProvidersSmokeTest.php:43  (new)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/BootProviders/BootProvidersSmokeTest.php:53  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/BootProviders/BootProvidersSmokeTest.php:77  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/BootProviders/BootProvidersSmokeTest.php:80  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/BootProviders/BootProvidersSmokeTest.php:92  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/BootProviders/BootProvidersSmokeTest.php:95  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/BootProviders/BootProvidersSmokeTest.php:186  (class-const-fetch)

MISSING: PrunedDependency  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/PruningSmokeTest.php:13  (constructor-param)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/PruningSmokeTest.php:35  (class-const-fetch)

MISSING: Rector\Config\RectorConfig  [MINOR]
  - /home/shomsy/projects/avax/components/Identity/Auth/rector.php:5  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Auth/phpVersion.php:7  (use-statement)
  - /home/shomsy/projects/avax/rector.php:5  (use-statement)

MISSING: Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPublicMethodParameterRector  [MINOR]
  - /home/shomsy/projects/avax/rector.php:6  (use-statement)
  - /home/shomsy/projects/avax/rector.php:39  (class-const-fetch)

MISSING: Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector  [MINOR]
  - /home/shomsy/projects/avax/rector.php:7  (use-statement)
  - /home/shomsy/projects/avax/rector.php:41  (class-const-fetch)

MISSING: Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector  [MINOR]
  - /home/shomsy/projects/avax/components/Identity/Auth/rector.php:6  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Auth/rector.php:21  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Identity/Auth/phpVersion.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Auth/phpVersion.php:23  (class-const-fetch)

MISSING: Rector\Set\ValueObject\LevelSetList  [MINOR]
  - /home/shomsy/projects/avax/rector.php:8  (use-statement)
  - /home/shomsy/projects/avax/rector.php:23  (class-const-fetch)

MISSING: Rector\Set\ValueObject\SetList  [MINOR]
  - /home/shomsy/projects/avax/components/Identity/Auth/rector.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Auth/rector.php:32  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Identity/Auth/rector.php:33  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Identity/Auth/rector.php:34  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Identity/Auth/rector.php:35  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Identity/Auth/rector.php:36  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Identity/Auth/rector.php:37  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Identity/Auth/phpVersion.php:9  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Auth/phpVersion.php:34  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Identity/Auth/phpVersion.php:35  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Identity/Auth/phpVersion.php:36  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Identity/Auth/phpVersion.php:37  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Identity/Auth/phpVersion.php:38  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Identity/Auth/phpVersion.php:39  (class-const-fetch)
  - /home/shomsy/projects/avax/rector.php:9  (use-statement)
  - /home/shomsy/projects/avax/rector.php:24  (class-const-fetch)
  - /home/shomsy/projects/avax/rector.php:25  (class-const-fetch)
  - /home/shomsy/projects/avax/rector.php:26  (class-const-fetch)
  - /home/shomsy/projects/avax/rector.php:27  (class-const-fetch)
  - /home/shomsy/projects/avax/rector.php:28  (class-const-fetch)
  - /home/shomsy/projects/avax/rector.php:29  (class-const-fetch)
  - /home/shomsy/projects/avax/rector.php:30  (class-const-fetch)
  - /home/shomsy/projects/avax/rector.php:31  (class-const-fetch)

MISSING: Rector\ValueObject\PhpVersion  [MINOR]
  - /home/shomsy/projects/avax/components/Identity/Auth/rector.php:8  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Auth/rector.php:30  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Identity/Auth/phpVersion.php:10  (use-statement)
  - /home/shomsy/projects/avax/components/Identity/Auth/phpVersion.php:32  (class-const-fetch)

MISSING: Redis  [CRITICAL]
  - /home/shomsy/projects/avax/components/Infrastructure/System/Capabilities/Cache/Redis.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Infrastructure/System/Capabilities/Cache/Redis.php:14  (property-type)
  - /home/shomsy/projects/avax/components/Infrastructure/System/Capabilities/Cache/Redis.php:24  (return-type)
  - /home/shomsy/projects/avax/components/Infrastructure/System/Capabilities/Cache/Redis.php:27  (new)
  - /home/shomsy/projects/avax/components/Operations/Queue/System/Capabilities/Queue/RedisQueue.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Operations/Queue/System/Capabilities/Queue/RedisQueue.php:12  (property-type)
  - /home/shomsy/projects/avax/components/Operations/Queue/System/Capabilities/Queue/RedisQueue.php:21  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Operations/Queue/System/Capabilities/Queue/RedisQueue.php:26  (new)
  - /home/shomsy/projects/avax/components/Operations/Queue/System/Capabilities/Queue/RedisQueue.php:43  (instanceof)
  - /home/shomsy/projects/avax/components/Operations/Queue/System/Capabilities/Queue/RedisQueue.php:59  (instanceof)
  - /home/shomsy/projects/avax/components/Operations/Queue/System/Capabilities/Queue/RedisQueue.php:78  (instanceof)
  - /home/shomsy/projects/avax/components/Operations/Resilience/System/Capabilities/RateLimiter/RedisRateLimiter.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/Operations/Resilience/System/Capabilities/RateLimiter/RedisRateLimiter.php:15  (property-type)
  - /home/shomsy/projects/avax/components/Operations/Resilience/System/Capabilities/RateLimiter/RedisRateLimiter.php:30  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Operations/Resilience/System/Capabilities/RateLimiter/RedisRateLimiter.php:35  (new)
  - /home/shomsy/projects/avax/components/Operations/Resilience/System/Capabilities/RateLimiter/RedisRateLimiter.php:65  (instanceof)
  - /home/shomsy/projects/avax/components/Operations/Resilience/System/Capabilities/RateLimiter/RedisRateLimiter.php:99  (instanceof)
  - /home/shomsy/projects/avax/components/Operations/Resilience/System/Capabilities/RateLimiter/RedisRateLimiter.php:118  (instanceof)
  - /home/shomsy/projects/avax/components/Operations/Resilience/System/Capabilities/RateLimiter/RedisRateLimiter.php:138  (instanceof)
  - /home/shomsy/projects/avax/components/HTTP/Session/System/Capabilities/Storage/RedisSessionStore.php:7  (use-statement)
  - /home/shomsy/projects/avax/components/HTTP/Session/System/Capabilities/Storage/RedisSessionStore.php:15  (property-type)
  - /home/shomsy/projects/avax/components/HTTP/Session/System/Capabilities/Storage/RedisSessionStore.php:30  (instanceof)
  - /home/shomsy/projects/avax/components/HTTP/Session/System/Capabilities/Storage/RedisSessionStore.php:47  (instanceof)
  - /home/shomsy/projects/avax/components/HTTP/Session/System/Capabilities/Storage/RedisSessionStore.php:60  (instanceof)
  - /home/shomsy/projects/avax/components/HTTP/Session/System/Capabilities/Storage/RedisSessionStore.php:71  (instanceof)
  - /home/shomsy/projects/avax/components/HTTP/Session/System/Capabilities/Storage/RedisSessionStore.php:90  (class-const-fetch)
  - /home/shomsy/projects/avax/components/HTTP/Session/System/Capabilities/Storage/RedisSessionStore.php:95  (new)
  - /home/shomsy/projects/avax/components/Application/Cache/System/Capabilities/Stores/RedisCacheStore.php:9  (use-statement)
  - /home/shomsy/projects/avax/components/Application/Cache/System/Capabilities/Stores/RedisCacheStore.php:14  (property-type)
  - /home/shomsy/projects/avax/components/Application/Cache/System/Capabilities/Stores/RedisCacheStore.php:24  (new)
  - /home/shomsy/projects/avax/components/Application/Cache/System/Capabilities/Stores/RedisCacheStore.php:103  (return-type)
  - /home/shomsy/projects/avax/framework/System/Capabilities/ExternalState/System/Capabilities/Drivers/Redis.php:8  (use-statement)
  - /home/shomsy/projects/avax/framework/System/Capabilities/ExternalState/System/Capabilities/Drivers/Redis.php:15  (property-type)
  - /home/shomsy/projects/avax/framework/System/Capabilities/ExternalState/System/Capabilities/Drivers/Redis.php:25  (new)

MISSING: ResolveGreeter  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/ResolveService/ResolveDependencySmokeTest.php:17  (constructor-param)

MISSING: SchemaCompatibilityDependency  [CRITICAL]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompiledSchemaCompatibilitySmokeTest.php:15  (constructor-param)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompiledSchemaCompatibilitySmokeTest.php:29  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompiledSchemaCompatibilitySmokeTest.php:30  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/CompiledSchemaCompatibilitySmokeTest.php:41  (class-const-fetch)

MISSING: ScopedService  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Capabilities/Runtime/Scopes/ManageScopesSmokeTest.php:14  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Capabilities/Runtime/Scopes/ManageScopesSmokeTest.php:29  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Capabilities/Runtime/Scopes/ManageScopesSmokeTest.php:35  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Capabilities/Runtime/Scopes/ManageScopesSmokeTest.php:36  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Capabilities/Runtime/Scopes/ManageScopesSmokeTest.php:45  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Capabilities/Runtime/Scopes/ManageScopesSmokeTest.php:49  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Capabilities/Runtime/Scopes/ManageScopesSmokeTest.php:52  (class-const-fetch)

MISSING: SecurityFailure  [CRITICAL]
  - /home/shomsy/projects/avax/test_decrypt_tmp.php:3  (extends)

MISSING: SharedOwnershipGateway  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/ResolveService/OwnershipCompositionSmokeTest.php:60  (class-const-fetch)

MISSING: StrictSharedFlowService  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/ResolveService/PolicyProfileSmokeTest.php:16  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/ResolveService/PolicyProfileSmokeTest.php:30  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/ResolveService/PolicyProfileSmokeTest.php:42  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/ResolveService/PolicyProfileSmokeTest.php:43  (class-const-fetch)

MISSING: WorkerSharedService  [MINOR]
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/WorkerRequestLifecycleSmokeTest.php:21  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/WorkerRequestLifecycleSmokeTest.php:23  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/WorkerRequestLifecycleSmokeTest.php:26  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/WorkerRequestLifecycleSmokeTest.php:41  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/WorkerRequestLifecycleSmokeTest.php:52  (class-const-fetch)
  - /home/shomsy/projects/avax/components/Application/Container/tests/Flows/CreateContainer/WorkerRequestLifecycleSmokeTest.php:53  (class-const-fetch)

MISSING: eftec\bladeone\BladeOne  [CRITICAL]
  - /home/shomsy/projects/avax/components/Presentation/View/TemplateEngine.php:16  (use-statement)
  - /home/shomsy/projects/avax/components/Presentation/View/TemplateEngine.php:21  (extends)
  - /home/shomsy/projects/avax/components/Presentation/View/TemplateEngine.php:43  (class-const-fetch)

=== SUMMARY ===
Defined: 3309
Missing: 727
  CRITICAL: 466
  MINOR: 261

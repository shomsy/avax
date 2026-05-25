<?php

declare(strict_types=1);

/**
 * PublicSurface baseline — pre-existing architectural debt that is temporarily accepted.
 *
 * Each entry groups findings by component area + check type.
 * This is NOT a suppression — the checker still detects every finding.
 * Baseline entries classify known findings as YELLOW (accepted debt) rather than
 * failing the gate. New/unclassified findings still fail as BLOCKER/HIGH.
 *
 * EXPIRY: This baseline expires when the Identity component completes Slice 3+
 * and the broader codebase PublicSurface remediation begins.
 *
 * OWNER: Architecture governance team.
 *
 * REMEDIATION TARGET: Future slices to migrate each PublicSurface to pure delegation.
 */

return [
    /**
     * Identity — 7 findings, all pre-existing static facade / lazy-construction patterns.
     * These belong to the deprecated static facade era and will be cleaned up during
     * Identity runtime convergence (Slice 3+).
     */
    'Identity' => [
        'hidden_construction' => [
            'reason' => 'Lazy in-memory construction and defensive exception creation in deprecated static facades',
            'owner' => 'Identity team',
            'expiry' => 'Slice 3+ (runtime convergence)',
            'remediation' => 'Wire production stores via DI, remove static facades',
            'files' => [
                'Access.php'             => 'Constructs PermissionDenied — defensive exception pattern',
                'ExternalIdentity.php'   => 'Constructs InMemoryExternalIdentityLinkStore — lazy default',
                'Credentials.php'        => 'Constructs InMemoryCredentialStore — lazy default',
                'shortcuts.php'          => 'Deprecated service locator helper',
            ],
        ],
        'service_locator' => [
            'reason' => 'Static singleton pattern in deprecated facades with reset() lifecycle',
            'owner' => 'Identity team',
            'expiry' => 'Slice 3+ (runtime convergence)',
            'remediation' => 'Remove static facades, use DI-resolved instances',
            'files' => [
                'shortcuts.php' => 'Deprecated service locator with DI migration path',
            ],
        ],
        'static_mutable_state' => [
            'reason' => 'Static facades with reset()/setInstance() lifecycle management',
            'owner' => 'Identity team',
            'expiry' => 'Slice 3+ (runtime convergence)',
            'remediation' => 'Remove static facades, use DI-resolved singletons',
            'files' => [
                'Auth.php'           => 'Has reset() lifecycle — YELLOW',
                'ExternalIdentity.php' => 'Has setInstance() lifecycle — YELLOW',
                'Credentials.php'    => 'Has setStore() lifecycle — YELLOW',
            ],
        ],
    ],

    /**
     * API — ~45 findings, mostly hidden construction in DSL-style public surfaces.
     * These components use inline construction as part of their DSL design.
     * Remediation belongs to API component refactor, not Identity governance.
     */
    'API' => [
        'hidden_construction' => [
            'reason' => 'DSL-style PublicSurface components construct their own sub-operations inline',
            'owner' => 'API team',
            'expiry' => 'API component PublicSurface remediation',
            'remediation' => 'Inject sub-operation dependencies via DI',
            'files' => [
                '*' => 'All API PublicSurface files — DSL pattern, not service locator abuse',
            ],
        ],
        'method_complexity' => [
            'reason' => 'DSL PublicSurface methods exceed 15 lines due to chaining',
            'owner' => 'API team',
            'expiry' => 'API component PublicSurface remediation',
            'remediation' => 'Break complex DSL methods into smaller delegated methods',
            'files' => [
                '*' => 'API PublicSurface DSL methods',
            ],
        ],
        'line_count' => [
            'reason' => 'DSL PublicSurface files exceed 150 lines due to many small delegation methods',
            'owner' => 'API team',
            'expiry' => 'API component PublicSurface remediation',
            'remediation' => 'Split large DSL PublicSurface into focused sub-facades',
            'files' => [
                '*' => 'API DSL PublicSurface files',
            ],
        ],
        'static_mutable_state' => [
            'reason' => 'API PublicSurface uses static singleton with reset lifecycle',
            'owner' => 'API team',
            'expiry' => 'API component PublicSurface remediation',
            'remediation' => 'Use DI-resolved instances',
            'files' => [
                'SchemaGeneration.php' => 'Static singleton with reset — YELLOW',
            ],
        ],
    ],

    /**
     * Operations — ~82 findings, mostly hidden construction in operational DSLs.
     */
    'Operations' => [
        'hidden_construction' => [
            'reason' => 'Operational DSL PublicSurface constructs sub-operations inline',
            'owner' => 'Operations team',
            'expiry' => 'Operations component PublicSurface remediation',
            'remediation' => 'Inject sub-operation dependencies via DI',
            'files' => [
                '*' => 'Operations DSL PublicSurface files',
            ],
        ],
        'business_logic' => [
            'reason' => 'Scheduler and QueueWorker PublicSurface contain runtime orchestration logic',
            'owner' => 'Operations team',
            'expiry' => 'Operations component PublicSurface remediation',
            'remediation' => 'Move orchestration to Flow, keep PublicSurface as delegation boundary',
            'files' => [
                'TaskRunner.php'   => 'Scheduler run() contains orchestration logic',
                'QueueWorker.php'  => 'QueueWorker process() contains orchestration logic',
            ],
        ],
        'method_complexity' => [
            'reason' => 'Operational DSL methods exceed 15 lines',
            'owner' => 'Operations team',
            'expiry' => 'Operations component PublicSurface remediation',
            'remediation' => 'Break complex methods into smaller delegated methods',
            'files' => [
                '*' => 'Operations DSL PublicSurface methods',
            ],
        ],
        'line_count' => [
            'reason' => 'Operations PublicSurface files exceed 150 lines due to orchestration logic',
            'owner' => 'Operations team',
            'expiry' => 'Operations component PublicSurface remediation',
            'remediation' => 'Split large orchestration PublicSurface into focused sub-facades',
            'files' => [
                'Saga.php' => '309 lines — saga orchestration DSL',
            ],
        ],
        'static_mutable_state' => [
            'reason' => 'Operations facades use static singletons with reset lifecycle',
            'owner' => 'Operations team',
            'expiry' => 'Operations component PublicSurface remediation',
            'remediation' => 'Use DI-resolved instances',
            'files' => [
                'Parallel.php'     => 'Static singleton with reset — YELLOW',
                'Scheduler.php'    => 'Static singleton with reset — YELLOW',
                'MessageBus.php'   => 'Static singleton with reset — YELLOW',
                'Concurrency.php'  => 'Static singleton with reset — YELLOW',
                'Realtime.php'     => 'Static singleton with reset — YELLOW',
            ],
        ],
        'service_locator' => [
            'reason' => 'Operations PublicSurface uses static singleton pattern with reset lifecycle',
            'owner' => 'Operations team',
            'expiry' => 'Operations component PublicSurface remediation',
            'remediation' => 'Use DI-resolved instances',
            'files' => [
                'MessageBus.php' => 'Static singleton with reset — YELLOW',
            ],
        ],
    ],

    /**
     * Application — ~84 findings, mostly hidden construction in Cache and Facade DSLs.
     */
    'Application' => [
        'hidden_construction' => [
            'reason' => 'Cache and Facade PublicSurface construct sub-operations inline',
            'owner' => 'Application team',
            'expiry' => 'Application component PublicSurface remediation',
            'remediation' => 'Inject sub-operation dependencies via DI',
            'files' => [
                '*' => 'Application PublicSurface files',
            ],
        ],
        'business_logic' => [
            'reason' => 'Cache read() contains cache-miss fallback logic',
            'owner' => 'Application team',
            'expiry' => 'Application component PublicSurface remediation',
            'remediation' => 'Move cache-miss logic to Flow, keep PublicSurface as delegation',
            'files' => [
                'ReadFromCache.php' => 'read() contains cache-miss fallback',
                'Cache.php'         => 'read() contains cache-miss fallback',
            ],
        ],
        'static_mutable_state' => [
            'reason' => 'Application facade and clock PublicSurface use static state without reset lifecycle',
            'owner' => 'Application team',
            'expiry' => 'Application component PublicSurface remediation',
            'remediation' => 'Use DI-resolved instances for facades, inject Clock interface',
            'files' => [
                'Storage.php'     => 'Facade static state — YELLOW debt',
                'Request.php'     => 'Facade static state — YELLOW debt',
                'Auth.php'        => 'Facade static state — YELLOW debt',
                'Route.php'       => 'Facade static state — YELLOW debt',
                'Session.php'     => 'Facade static state — YELLOW debt',
                'SystemClock.php' => 'Mutable static clock — YELLOW debt',
                'Clock.php'       => 'Mutable static clock — YELLOW debt',
                'Pipeline.php'    => 'Static singleton with reset — YELLOW',
                'Container.php'   => 'Static singleton with reset — YELLOW',
                'FeatureFlags.php' => 'Static singleton with reset — YELLOW',
                'CompiledCache.php' => 'Static singleton with reset — YELLOW',
                'Cache.php'       => 'Static singleton with reset — YELLOW',
            ],
        ],
        'service_locator' => [
            'reason' => 'Application PublicSurface uses static singleton pattern with reset lifecycle',
            'owner' => 'Application team',
            'expiry' => 'Application component PublicSurface remediation',
            'remediation' => 'Use DI-resolved instances',
            'files' => [
                'Container.php'   => 'Static singleton with reset — YELLOW',
                'CompiledCache.php' => 'Static singleton with reset — YELLOW',
                'Cache.php'       => 'Static singleton with reset — YELLOW',
            ],
        ],
        'method_complexity' => [
            'reason' => 'Application PublicSurface methods exceed 15 lines',
            'owner' => 'Application team',
            'expiry' => 'Application component PublicSurface remediation',
            'remediation' => 'Break complex methods into smaller delegated methods',
            'files' => [
                'Translator.php'    => 'get() 18 lines, parseKey() 18 lines, makeReplacements() 16 lines',
                'ReadFromCache.php' => 'read() 16 lines',
                'AvaxCache.php'     => 'remember() 33 lines, protectedLoad() 33 lines, set() 33 lines, get() 29 lines, delete() 20 lines',
                'Cache.php'         => 'read() 20 lines',
            ],
        ],
        'line_count' => [
            'reason' => 'Application PublicSurface files exceed 150 lines',
            'owner' => 'Application team',
            'expiry' => 'Application component PublicSurface remediation',
            'remediation' => 'Split large PublicSurface into focused sub-facades',
            'files' => [
                'Pipeline.php'  => '173 lines',
                'Text.php'      => '219 lines',
                'shortcuts.php' => '216 lines',
                'AvaxCache.php' => '335 lines',
            ],
        ],
    ],

    /**
     * DataStack — ~40 findings, mostly hidden construction in database DSLs.
     */
    'DataStack' => [
        'hidden_construction' => [
            'reason' => 'Database and persistence PublicSurface construct query/ORM objects inline',
            'owner' => 'DataStack team',
            'expiry' => 'DataStack component PublicSurface remediation',
            'remediation' => 'Inject query/ORM dependencies via DI or builder pattern',
            'files' => [
                '*' => 'DataStack PublicSurface files',
            ],
        ],
        'static_mutable_state' => [
            'reason' => 'DataStack PublicSurface uses static state without reset lifecycle',
            'owner' => 'DataStack team',
            'expiry' => 'DataStack component PublicSurface remediation',
            'remediation' => 'Use DI-resolved instances',
            'files' => [
                'DataTransfer.php' => 'Static state without reset — YELLOW debt',
            ],
        ],
        'line_count' => [
            'reason' => 'DataStack PublicSurface files exceed 150 lines due to many DSL methods',
            'owner' => 'DataStack team',
            'expiry' => 'DataStack component PublicSurface remediation',
            'remediation' => 'Split large PublicSurface into focused sub-facades',
            'files' => [
                'DataTransfer.php' => '151 lines — DSL methods',
            ],
        ],
    ],

    /**
     * HTTP — ~37 findings, mostly hidden construction in middleware and request DSLs.
     */
    'HTTP' => [
        'hidden_construction' => [
            'reason' => 'HTTP PublicSurface constructs middleware/pipeline objects inline',
            'owner' => 'HTTP team',
            'expiry' => 'HTTP component PublicSurface remediation',
            'remediation' => 'Inject middleware dependencies via DI',
            'files' => [
                '*' => 'HTTP PublicSurface files',
            ],
        ],
        'business_logic' => [
            'reason' => 'SecureRequest runLifecycle() contains request lifecycle orchestration',
            'owner' => 'HTTP team',
            'expiry' => 'HTTP component PublicSurface remediation',
            'remediation' => 'Move lifecycle orchestration to Flow',
            'files' => [
                'SecureRequest.php' => 'runLifecycle() contains request orchestration',
            ],
        ],
        'method_complexity' => [
            'reason' => 'HTTP PublicSurface methods exceed 15 lines due to orchestration logic',
            'owner' => 'HTTP team',
            'expiry' => 'HTTP component PublicSurface remediation',
            'remediation' => 'Break complex methods into smaller delegated methods',
            'files' => [
                'Router.php'         => 'dispatch() is 30 lines',
                'SecureRequest.php'  => 'runLifecycle() is 48 lines',
                'Session.php'        => 'start() is 34 lines, touch() is 18 lines',
                'SessionScope.php'   => 'start() is 16 lines',
                'Uri.php'            => 'build() is 20 lines',
                'CsvFormatter.php'   => 'format() is 25 lines',
            ],
        ],
        'line_count' => [
            'reason' => 'HTTP PublicSurface files exceed 150 lines due to many methods',
            'owner' => 'HTTP team',
            'expiry' => 'HTTP component PublicSurface remediation',
            'remediation' => 'Split large PublicSurface into focused sub-facades',
            'files' => [
                'Router.php'        => '191 lines',
                'SecureRequest.php' => '168 lines',
                'Session.php'       => '233 lines',
                'HttpContext.php'   => '151 lines',
                'Request.php'       => '231 lines',
            ],
        ],
        'static_mutable_state' => [
            'reason' => 'HTTP PublicSurface uses static singleton with reset lifecycle',
            'owner' => 'HTTP team',
            'expiry' => 'HTTP component PublicSurface remediation',
            'remediation' => 'Use DI-resolved instances',
            'files' => [
                'ApiVersion.php' => 'Static singleton with reset — YELLOW',
            ],
        ],
    ],

    /**
     * Security — ~19 findings, mostly hidden construction in security DSLs.
     */
    'Security' => [
        'hidden_construction' => [
            'reason' => 'Security PublicSurface constructs policy/engine objects inline',
            'owner' => 'Security team',
            'expiry' => 'Security component PublicSurface remediation',
            'remediation' => 'Inject security dependencies via DI',
            'files' => [
                '*' => 'Security PublicSurface files',
            ],
        ],
        'static_mutable_state' => [
            'reason' => 'Security facades use static singletons with reset lifecycle',
            'owner' => 'Security team',
            'expiry' => 'Security component PublicSurface remediation',
            'remediation' => 'Use DI-resolved instances',
            'files' => [
                '*' => 'Security static facade files',
            ],
        ],
        'service_locator' => [
            'reason' => 'Security PublicSurface uses static singleton pattern with reset lifecycle',
            'owner' => 'Security team',
            'expiry' => 'Security component PublicSurface remediation',
            'remediation' => 'Use DI-resolved instances',
            'files' => [
                'Secrets.php' => 'Static singleton with reset — YELLOW',
            ],
        ],
    ],

    /**
     * SystemDesign — ~20 findings, all hidden construction in design DSL.
     */
    'SystemDesign' => [
        'hidden_construction' => [
            'reason' => 'SystemDesignKit constructs estimation/validation objects inline as a DSL',
            'owner' => 'Architecture team',
            'expiry' => 'SystemDesign component remediation',
            'remediation' => 'Inject design operation dependencies via DI',
            'files' => [
                '*' => 'SystemDesignKit PublicSurface',
            ],
        ],
        'method_complexity' => [
            'reason' => 'SystemDesignKit validation methods exceed 15 lines',
            'owner' => 'Architecture team',
            'expiry' => 'SystemDesign component remediation',
            'remediation' => 'Break complex validation methods into smaller delegated methods',
            'files' => [
                'SystemDesignKit.php' => 'validateReferenceArchitecture() is 58 lines',
            ],
        ],
        'line_count' => [
            'reason' => 'SystemDesignKit exceeds 150 lines due to many design estimation methods',
            'owner' => 'Architecture team',
            'expiry' => 'SystemDesign component remediation',
            'remediation' => 'Split SystemDesignKit into focused sub-facades',
            'files' => [
                'SystemDesignKit.php' => '492 lines',
            ],
        ],
    ],

    /**
     * DeveloperTools — ~9 findings, hidden construction in tool DSLs.
     */
    'DeveloperTools' => [
        'hidden_construction' => [
            'reason' => 'DeveloperTools PublicSurface constructs tool objects inline',
            'owner' => 'DeveloperTools team',
            'expiry' => 'DeveloperTools component remediation',
            'remediation' => 'Inject tool dependencies via DI',
            'files' => [
                '*' => 'DeveloperTools PublicSurface files',
            ],
        ],
        'method_complexity' => [
            'reason' => 'HealthCheck readiness method exceeds 15 lines',
            'owner' => 'DeveloperTools team',
            'expiry' => 'DeveloperTools component remediation',
            'remediation' => 'Break complex health check methods into smaller delegated methods',
            'files' => [
                'HealthCheck.php' => 'readiness() is 21 lines',
            ],
        ],
        'static_mutable_state' => [
            'reason' => 'DeveloperTools PublicSurface uses static singleton with reset lifecycle',
            'owner' => 'DeveloperTools team',
            'expiry' => 'DeveloperTools component remediation',
            'remediation' => 'Use DI-resolved instances',
            'files' => [
                'HealthCheck.php' => 'Static singleton with reset — YELLOW',
            ],
        ],
    ],

    /**
     * CLI — ~5 findings, hidden construction in CLI DSLs.
     */
    'CLI' => [
        'hidden_construction' => [
            'reason' => 'CLI PublicSurface constructs command objects inline',
            'owner' => 'CLI team',
            'expiry' => 'CLI component remediation',
            'remediation' => 'Inject command dependencies via DI',
            'files' => [
                '*' => 'CLI PublicSurface files',
            ],
        ],
        'method_complexity' => [
            'reason' => 'CLI PublicSurface methods exceed 15 lines due to command routing',
            'owner' => 'CLI team',
            'expiry' => 'CLI component remediation',
            'remediation' => 'Break complex methods into smaller delegated methods',
            'files' => [
                'Command.php' => 'run() is 18 lines',
                'Console.php' => 'run() is 45 lines, list() is 34 lines',
            ],
        ],
        'line_count' => [
            'reason' => 'CLI PublicSurface files exceed 150 lines due to many command methods',
            'owner' => 'CLI team',
            'expiry' => 'CLI component remediation',
            'remediation' => 'Split large CLI PublicSurface into focused sub-facades',
            'files' => [
                'Command.php' => '223 lines',
                'Console.php' => '163 lines',
            ],
        ],
    ],

    /**
     * Foundation — ~4 findings, hidden construction in serialization DSL.
     */
    'Foundation' => [
        'hidden_construction' => [
            'reason' => 'CallableSerialization PublicSurface constructs serialization objects inline',
            'owner' => 'Foundation team',
            'expiry' => 'Foundation component remediation',
            'remediation' => 'Inject serialization dependencies via DI',
            'files' => [
                '*' => 'CallableSerialization PublicSurface',
            ],
        ],
        'static_mutable_state' => [
            'reason' => 'Foundation PublicSurface uses static singleton with reset lifecycle',
            'owner' => 'Foundation team',
            'expiry' => 'Foundation component remediation',
            'remediation' => 'Use DI-resolved instances',
            'files' => [
                'CallableSerialization.php' => 'Static singleton with reset — YELLOW',
            ],
        ],
    ],

    /**
     * Integration — 1 finding, hidden construction in object storage DSL.
     */
    'Integration' => [
        'hidden_construction' => [
            'reason' => 'ObjectStorage PublicSurface constructs health check inline',
            'owner' => 'Integration team',
            'expiry' => 'Integration component remediation',
            'remediation' => 'Inject health check dependency via DI',
            'files' => [
                '*' => 'ObjectStorage PublicSurface',
            ],
        ],
    ],
];

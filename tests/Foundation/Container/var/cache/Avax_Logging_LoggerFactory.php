<?php

declare(strict_types=1);

use Avax\Components\Application\Container\DependencyInjection\Capability\Prototypes\Model\ServicePrototype;
use Avax\Logging\LoggerFactory;

return ServicePrototype::__set_state(array: [
                                                'class'              => LoggerFactory::class,
                                                'constructor'        => null,
                                                'injectedProperties' => [
                                                ],
                                                'injectedMethods'    => [
                                                ],
                                                'isInstantiable'     => true,
                                            ]);

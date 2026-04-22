<?php

use Avax\Container\DependencyInjection\Capability\Prototypes\Model\ServicePrototype;
use Avax\Logging\LoggerFactory;

return ServicePrototype::__set_state(array: [
                                                'class' => LoggerFactory::class,
                                                'constructor'        => null,
                                                'injectedProperties' => [
                                                ],
                                                'injectedMethods'    => [
                                                ],
                                                'isInstantiable'     => true,
                                            ]);

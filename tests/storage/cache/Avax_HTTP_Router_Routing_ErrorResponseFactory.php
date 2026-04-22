<?php

use Avax\Container\Features\Think\Model\ServicePrototype;
use Avax\HTTP\Router\Routing\ErrorResponseFactory;

return ServicePrototype::__set_state(array: [
                                                'class' => ErrorResponseFactory::class,
                                                'constructor'        => null,
                                                'injectedProperties' =>
                                                    [
                                                    ],
                                                'injectedMethods'    =>
                                                    [
                                                    ],
                                                'isInstantiable'     => true,
                                            ]);

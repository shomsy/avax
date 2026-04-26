<?php

use components\Container\Features\Think\Model\ServicePrototype;
use components\HTTP\Router\Routing\ErrorResponseFactory;
use components\HTTP\Router\System\Flows\RunRoute\Responses\ErrorResponseFactory;

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

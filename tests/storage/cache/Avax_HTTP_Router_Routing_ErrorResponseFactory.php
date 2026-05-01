<?php

declare(strict_types=1);

use components\Container\Features\Think\Model\ServicePrototype;
use components\HTTP\Router\Routing\ErrorResponses;
use components\HTTP\Router\System\Flows\RunRoute\Responses\ErrorResponses;

return ServicePrototype::__set_state(array: [
                                                'class'              => ErrorResponses::class,
                                                'constructor'        => null,
                                                'injectedProperties' => [
                                                    ],
                                                'injectedMethods' => [
                                                    ],
                                                'isInstantiable'  => true,
                                            ]);

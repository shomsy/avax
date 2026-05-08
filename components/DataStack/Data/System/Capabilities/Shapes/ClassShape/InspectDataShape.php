<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Shapes\ClassShape;

use Avax\Components\DataStack\Data\System\Capabilities\Coercion\DtoSystem\Configuration\DataTransferConfig;

final readonly class InspectDataShape
{
    public function __construct(
        private ?DataTransferConfig $dataTransferConfig = null,
        private CacheDataShape $cacheDataShape = new CacheDataShape(),
    ) {
    }

    /**
     * @param  class-string  $class
     */
    public function inspect(string $class): DataShape
    {
        $config = $this->dataTransferConfig ?? DataTransferConfig::default();
        $cacheKey = $class.':'.spl_object_id(object: $config);

        return $this->cacheDataShape->remember(
            key    : $cacheKey,
            builder: static fn (): DataShape => new ReadClassDataShape()->read(class: $class, dataTransferConfig: $config),
        );
    }
}

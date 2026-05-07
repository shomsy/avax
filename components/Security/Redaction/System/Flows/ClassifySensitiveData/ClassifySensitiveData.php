<?php

declare(strict_types=1);

namespace Avax\Components\Security\Redaction\System\Flows\ClassifySensitiveData;

use Avax\Components\Security\Redaction\System\Capabilities\DataClassifier\DataClassifier;

final readonly class ClassifySensitiveData
{
    public function __construct(
        private DataClassifier $classifier = new DataClassifier(),
    ) {}

    /**
     * @return list<array{type:string,value:string,confidence:float}>
     */
    public function execute(string $data) : array
    {
        return $this->classifier->classify(data: $data);
    }
}

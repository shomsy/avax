<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Body\Problem;

use Avax\HTTP\Response\Capabilities\Body\Json\EncodeJsonBody;
use JsonException;

final class EncodeProblemDetails
{
    /**
     * @throws JsonException
     */
    public function __invoke(ProblemDetails $problemDetails) : string
    {
        return (new EncodeJsonBody())($problemDetails->toArray());
    }
}

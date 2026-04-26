<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Flows\BuildResponse;

use Avax\HTTP\Response\Capabilities\Body\Problem\EncodeProblemDetails;
use Avax\HTTP\Response\Capabilities\Body\Problem\ProblemDetails;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class BuildProblemResponse
{
    public function __invoke(
        string      $title,
        int|null    $status = null,
        string|null $detail = null,
        string|null $type = null,
        array       $extensions = [],
    ) : ResponseInterface
    {
        $status ??= 400;
        $detail ??= '';
        $type   ??= 'about:blank';
        try {
            $payload = new EncodeProblemDetails()(
                problemDetails: new ProblemDetails(
                                    title     : $title,
                                    status    : $status,
                                    detail    : $detail,
                                    type      : $type,
                                    extensions: $extensions,
                                )
            );
        } catch (JsonException $exception) {
            throw new RuntimeException(
                message : 'Unable to encode problem-details response body.',
                code    : $exception->getCode(),
                previous: $exception,
            );
        }

        return new BuildResponse()(
            status : $status,
            headers: ['Content-Type' => 'application/problem+json'],
            body   : $payload,
        );
    }
}

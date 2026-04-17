<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Request\Inputs\Examples;

use Avax\DataHandling\ObjectHandling\DTO\DTOValidationException;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs\Examples\CreateNewProjectRequest;
use PHPUnit\Framework\TestCase;

final class CreateNewProjectRequestTest extends TestCase
{
    public function test_from_request_hydrates_validated_dto(): void
    {
        $request = Request::create(
            queryParams: ['projectNumber' => 42],
            parsedBody: ['projectName' => 'My Project', 'priority' => 5],
        );

        $dto = $request->as(CreateNewProjectRequest::class);

        $this->assertSame(expected: 'My Project', actual: $dto->projectName);
        $this->assertSame(expected: 42, actual: $dto->projectNumber);
        $this->assertSame(expected: 5, actual: $dto->priority);
    }

    public function test_validates_required_project_name(): void
    {
        $this->expectException(DTOValidationException::class);

        $request = Request::create(
            parsedBody: ['projectNumber' => 1],
        );

        $request->as(CreateNewProjectRequest::class);
    }

    public function test_validates_required_project_number(): void
    {
        $this->expectException(DTOValidationException::class);

        $request = Request::create(
            parsedBody: ['projectName' => 'Test'],
        );

        $request->as(CreateNewProjectRequest::class);
    }

    public function test_validates_project_name_min_length(): void
    {
        $this->expectException(DTOValidationException::class);

        $request = Request::create(
            parsedBody: ['projectName' => 'AB', 'projectNumber' => 1],
        );

        $request->as(CreateNewProjectRequest::class);
    }

    public function test_validates_project_number_is_integer(): void
    {
        $this->expectException(DTOValidationException::class);

        $request = Request::create(
            parsedBody: ['projectName' => 'Test', 'projectNumber' => 'not-a-number'],
        );

        $request->as(CreateNewProjectRequest::class);
    }

    public function test_priority_has_default_value(): void
    {
        $request = Request::create(
            parsedBody: ['projectName' => 'Test', 'projectNumber' => 1],
        );

        $dto = $request->as(CreateNewProjectRequest::class);

        $this->assertSame(expected: 0, actual: $dto->priority);
    }

    /**
     * @throws \ReflectionException
     */
    public function test_from_inputs_creates_dto_from_array(): void
    {
        $dto = CreateNewProjectRequest::fromInputs([
            'projectName' => 'Test Project',
            'projectNumber' => 10,
            'priority' => 3,
        ]);

        $this->assertSame(expected: 'Test Project', actual: $dto->projectName);
        $this->assertSame(expected: 10, actual: $dto->projectNumber);
        $this->assertSame(expected: 3, actual: $dto->priority);
    }
}

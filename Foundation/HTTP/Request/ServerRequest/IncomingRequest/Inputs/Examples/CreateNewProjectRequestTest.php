<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Request\Inputs\Examples;

use Avax\DataHandling\ObjectHandling\DTO\DTOValidationException;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\Inputs\Examples\CreateNewProjectRequest;
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

        $this->assertSame('My Project', $dto->projectName);
        $this->assertSame(42, $dto->projectNumber);
        $this->assertSame(5, $dto->priority);
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

        $this->assertSame(0, $dto->priority);
    }

    public function test_from_inputs_creates_dto_from_array(): void
    {
        $dto = CreateNewProjectRequest::fromInputs([
            'projectName' => 'Test Project',
            'projectNumber' => 10,
            'priority' => 3,
        ]);

        $this->assertSame('Test Project', $dto->projectName);
        $this->assertSame(10, $dto->projectNumber);
        $this->assertSame(3, $dto->priority);
    }
}

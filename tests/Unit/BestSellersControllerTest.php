<?php

namespace Tests\Unit;

use App\Service\NYTService;
use Mockery;
use Tests\TestCase;

class BestSellersControllerTest extends TestCase
{

    /** @test */
    public function it_fetches_best_sellers_successfully()
    {
        // Arrange: Mock the NYTService response
        $mockedResponse = ['status' => 'OK', 'results' => ['title' => 'Test Book']];
        $nytServiceMock = Mockery::mock(NYTService::class);
        $nytServiceMock->shouldReceive('fetchBestSellers')
            ->once()
            ->with(['author' => 'Test Author'])
            ->andReturn($mockedResponse);

        $this->instance(NYTService::class, $nytServiceMock);

        // Act: Call the controller endpoint
        $response = $this->getJson('/api/v1/nyt/best-sellers?author=Test%20Author');

        // Assert: Validate the response
        $response->assertStatus(200);
        $response->assertJson($mockedResponse);
    }

    /** @test */
    public function it_handles_validation_errors_gracefully()
    {
        // Act: Call the controller endpoint with invalid query parameters
        $response = $this->getJson('/api/v1/nyt/best-sellers?author=&isbn[]=invalid');

        // Assert: Ensure validation fails
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['isbn.0']);
    }

    /** @test */
    public function it_removes_empty_isbn_field_before_sending_to_service()
    {
        // Arrange: Mock the NYTService response
        $mockedResponse = ['status' => 'OK', 'results' => ['title' => 'Test Book']];
        $nytServiceMock = Mockery::mock(NYTService::class);
        $nytServiceMock->shouldReceive('fetchBestSellers')
            ->once()
            ->with(['author' => 'Test Author']) // No `isbn` field since it was empty
            ->andReturn($mockedResponse);

        $this->instance(NYTService::class, $nytServiceMock);

        // Act: Call the controller endpoint
        $response = $this->getJson('/api/v1/nyt/best-sellers?author=Test%20Author&isbn[]=');

        // Assert: Validate the response
        $response->assertStatus(200);
        $response->assertJson($mockedResponse);
    }

    /** @test */
    public function it_converts_isbn_array_to_string_before_sending_to_service()
    {
        // Arrange: Mock the NYTService response
        $mockedResponse = ['status' => 'OK', 'results' => ['title' => 'Test Book']];
        $nytServiceMock = Mockery::mock(NYTService::class);
        $nytServiceMock->shouldReceive('fetchBestSellers')
            ->once()
            ->with(['author' => 'Test Author', 'isbn' => '1234567890;0987654321'])
            ->andReturn($mockedResponse);

        $this->instance(NYTService::class, $nytServiceMock);

        // Act: Call the controller endpoint
        $response = $this->getJson('/api/v1/nyt/best-sellers?author=Test%20Author&isbn[]=1234567890&isbn[]=0987654321');

        // Assert: Validate the response
        $response->assertStatus(200);
        $response->assertJson($mockedResponse);
    }

}

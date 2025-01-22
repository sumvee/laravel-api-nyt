<?php

namespace Tests\Unit;

use App\Exception\NYTApiException;
use App\Service\NYTService;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery;
use ReflectionClass;
use ReflectionException;
use Tests\TestCase;

class NYTServiceTest extends TestCase
{
    protected NYTService $nytService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->nytService = new NYTService();
        Cache::clear(); // Clear cache before each test
    }

    /** @test */
    /** @test */
    public function it_returns_cached_results_if_available()
    {
        // Arrange: Mock Cache and expected response
        $params = ['author' => 'Test Author'];
        $cacheKey = 'nyt_best_sellers_' . md5(json_encode($params));
        $expectedResponse = ['status' => 'OK', 'results' => ['title' => 'Test Book']];

        // Mock Cache::remember to return the expected cached value
        Cache::shouldReceive('remember')
            ->with($cacheKey, Mockery::any(), Mockery::any())
            ->andReturn($expectedResponse);

        // Act
        $result = $this->nytService->fetchBestSellers($params);

        // Assert
        $this->assertEquals($expectedResponse, $result);
    }


    /** @test */
    public function it_fetches_data_from_api_when_not_in_cache()
    {
        // Arrange: Mock HTTP response and Cache
        $params = ['author' => 'New Author'];
        $fakeResponse = ['status' => 'OK', 'results' => ['title' => 'New Test Book']];
        Http::fake([
            config('services.nyt.base_uri') . '*' => Http::response($fakeResponse, 200),
        ]);

        // Act
        $result = $this->nytService->fetchBestSellers($params);

        // Assert
        $this->assertEquals($fakeResponse, $result);
    }

    /** @test */
    public function it_logs_error_when_exception_occurs()
    {
        // Arrange: Simulate an exception during API fetch
        $params = ['title' => 'Invalid Book'];
        Http::fake([
            config('services.nyt.base_uri') . '*' => Http::response([], 500),
        ]);
        Log::shouldReceive('error')->once();

        // Act
        $result = $this->nytService->fetchBestSellers($params);

        // Assert
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Unable to fetch best sellers at this time. Please try again later.', $result['error']);
    }

    /** @test */
    public function it_throws_nyt_api_exception_for_invalid_response()
    {
        // Arrange: Simulate a failed response
        $params = ['isbn' => 'invalid'];
        $fakeResponse = ['status' => 'ERROR'];
        Http::fake([
            config('services.nyt.base_uri') . '*' => Http::response($fakeResponse, 400),
        ]);

        // Expect exception
        $this->expectException(NYTApiException::class);

        // Act
        $this->nytService->fetchAPIData($params);
    }

    /** @test
     * @throws ReflectionException
     */
    public function it_validates_response_has_ok()
    {
        // Arrange: Mock a successful and failed response
        $successfulResponse = new \Illuminate\Http\Client\Response(
            new Response(200, [], json_encode(['status' => 'OK']))
        );
        $failedResponse = new \Illuminate\Http\Client\Response(
            new Response(400, [], json_encode(['status' => 'ERROR']))
        );

        // Act & Assert: Validate the responses using the private method
        $this->assertTrue($this->invokePrivateMethod($this->nytService, 'responseHasOk', [$successfulResponse]));
        $this->assertFalse($this->invokePrivateMethod($this->nytService, 'responseHasOk', [$failedResponse]));
    }


    /**
     * Helper method to invoke private methods
     * @throws ReflectionException
     */
    protected function invokePrivateMethod(object $object, string $methodName, array $parameters)
    {
        $reflection = new ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }


}

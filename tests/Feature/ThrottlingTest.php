<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ThrottlingTest extends TestCase
{

    /** @test */
    public function it_allows_requests_within_rate_limit()
    {
        // Arrange: Set the rate limit to 5 requests per minute
        Config::set('services.nyt.api_rate_limit', 5);

        // Act & Assert: Send 5 requests within the rate limit
        for ($i = 0; $i < 5; $i++) {
            $response = $this->getJson('/api/v1/nyt/best-sellers?author=Test%20Author');
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function it_blocks_requests_exceeding_rate_limit()
    {
        // Arrange: Set the rate limit to 5 requests per minute
        Config::set('services.nyt.api_rate_limit', 5);

        // Act: Send 6 requests, exceeding the rate limit
        for ($i = 0; $i < 5; $i++) {
            $this->getJson('/api/v1/nyt/best-sellers?author=Test%20Author')->assertStatus(200);
        }

        // Send the 6th request
        $response = $this->getJson('/api/v1/nyt/best-sellers?author=Test%20Author');

        // Assert: Validate the response is blocked
        $response->assertStatus(429); // HTTP 429: Too Many Requests
        $response->assertJson([
            'message' => 'Too Many Attempts.',
        ]);
    }
}

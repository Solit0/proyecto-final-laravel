<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApplicationTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_laravel_starts_without_a_database_connection(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_health_endpoint_responds_successfully(): void
    {
        $this->get('/up')->assertOk();
    }
}

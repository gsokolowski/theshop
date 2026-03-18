<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Fails fast if tests are running against the wrong database. Ensures phpunit.xml
     * env vars (DB_CONNECTION, DB_DATABASE) are used and we are not touching dev/prod DB.
     */
    public function test_database_is_in_memory(): void
    {
        $this->assertSame(':memory:', config('database.connections.'.config('database.default').'.database'));
    }

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}

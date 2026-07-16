<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Feature tests call post()/put()/patch/delete() without browser CSRF tokens.
        $this->withoutMiddleware(ValidateCsrfToken::class);

        // Avoid stale product list cache leaking between tests (array driver persists in-process).
        Cache::flush();
    }
}

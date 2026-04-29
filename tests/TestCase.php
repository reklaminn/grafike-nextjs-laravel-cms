<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Disable Vite manifest requirement in tests
        $this->withoutVite();

        // CSRF tokens are not available in the test environment; bypass the
        // middleware so POST / PUT / DELETE requests don't return 419.
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }
}

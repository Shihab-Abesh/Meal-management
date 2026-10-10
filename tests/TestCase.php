<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Arr;
use Illuminate\Testing\TestResponse;
use Inertia\Support\SessionKey;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Read a value flashed with Inertia::flash(), e.g. $response->inertiaFlash('importResult').
        TestResponse::macro('inertiaFlash', function (?string $key = null) {
            /** @var TestResponse $this */
            $flash = $this->session()->get(SessionKey::FLASH_DATA, []);

            return $key === null ? $flash : Arr::get($flash, $key);
        });
    }
}

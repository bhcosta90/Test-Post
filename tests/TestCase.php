<?php

declare(strict_types = 1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $dbPath = base_path(env('DB_DATABASE', 'testing'));

        if (!file_exists($dbPath)) {
            touch($dbPath);
        }

        $this->artisan('migrate');
    }
}

<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'session.driver' => 'array',
            'database.default' => env('DB_CONNECTION', 'pgsql'),
            'database.connections.pgsql.database' => env('DB_DATABASE', 'internish_test'),
            'database.connections.pgsql.username' => env('DB_USERNAME', 'postgres'),
            'database.connections.pgsql.password' => env('DB_PASSWORD', ''),
        ]);
    }
}

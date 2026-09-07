<?php

namespace JeffersonGoncalves\Livestorm\Tests;

use JeffersonGoncalves\Livestorm\LivestormServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivestormServiceProvider::class,
        ];
    }
}

<?php

namespace Jeffersongoncalves\LaravelLivestorm\Tests;

use Jeffersongoncalves\LaravelLivestorm\LaravelLivestormServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelLivestormServiceProvider::class,
        ];
    }
}

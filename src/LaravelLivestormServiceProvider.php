<?php

namespace Jeffersongoncalves\LaravelLivestorm;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelLivestormServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-livestorm')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations();
    }
}

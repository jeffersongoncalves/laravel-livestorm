<?php

namespace JeffersonGoncalves\Livestorm;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LivestormServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('livestorm')
            ->hasConfigFile();
    }
}

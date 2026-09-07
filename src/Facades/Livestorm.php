<?php

namespace JeffersonGoncalves\Livestorm\Facades;

use Illuminate\Support\Facades\Facade;
use JeffersonGoncalves\Livestorm\LivestormClient;

/**
 * @see LivestormClient
 */
class Livestorm extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'livestorm';
    }
}

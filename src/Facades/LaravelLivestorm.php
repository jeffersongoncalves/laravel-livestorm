<?php

namespace Jeffersongoncalves\LaravelLivestorm\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Jeffersongoncalves\LaravelLivestorm\LaravelLivestorm
 */
class LaravelLivestorm extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-livestorm';
    }
}

<?php

namespace JacobTilly\LaraFort\Facades;

use Illuminate\Support\Facades\Facade;

class LaraFort extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'larafort';
    }
}

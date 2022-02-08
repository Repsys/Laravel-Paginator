<?php

namespace Leonidark\Paginator;

use Illuminate\Support\Facades\Facade;

class PaginatorFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'paginator';
    }
}

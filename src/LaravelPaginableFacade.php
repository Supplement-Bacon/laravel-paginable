<?php

namespace SupplementBacon\LaravelPaginable;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SupplementBacon\LaravelPaginable\Skeleton\SkeletonClass
 */
class LaravelPaginableFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-paginable';
    }
}

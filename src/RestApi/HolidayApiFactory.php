<?php

namespace App\RestApi;

/**
 * Holiday API factory
 */
class HolidayApiFactory
{

    /**
     * Creates an instance of the API service
     *
     * @return HolidayApiInterface instance
     */
    public static function create(): HolidayApiInterface
    {
        return new CachedHolidayApi();
    }

}
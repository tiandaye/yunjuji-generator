<?php

/**
 * @Author: admin
 * @Date:   2017-09-13 17:59:44
 * @Last Modified by:   admin
 * @Last Modified time: 2017-09-21 18:17:14
 */
namespace Yunjuji\Generator\Facades;

use Illuminate\Support\Facades\Facade;

class YunjujiGenerator extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Yunjuji\Generator\YunjujiGenerator::class;
        // return 'yunjujigenerator';
    }
}

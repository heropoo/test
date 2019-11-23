<?php
/**
 * Created by PhpStorm.
 * User: Heropoo
 * Date: 2019/3/16
 * Time: 23:56
 */
namespace App\Commands;

class PingCommand
{
    public function ping($string = null){
        return is_null($string) ? 'pong' : $string;
    }
}
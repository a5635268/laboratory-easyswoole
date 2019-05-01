<?php
namespace App\Libs;
use \EasySwoole\EasySwoole\Config;

/**
 * 兼容yaconf的conf获取器
 * Class Conf
 * @package Libs
 */
class Conf
{
    public static function get(string $arg)
    {
        if(extension_loaded('yaconf') && \Yaconf::has(strtolower($arg))){
            return \Yaconf::get(strtolower($arg));
        }
        # todo: 截断前面的项目名称字符串
        $conf = Config::getInstance()->getConf($arg);
        return $conf;
    }
}
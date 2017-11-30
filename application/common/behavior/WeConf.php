<?php
namespace app\common\behavior;

class WeConf
{
    function run()
    {
        $wxconf = [
            //'配置项'=>'配置值'
            "TOKEN"          => "whtoken",
            "APPID"          => "wxe01997548610a9dc",
            "APPSECRET"      => "7da6990ed964eb5ab88aa2f651b07a69",

            //(2)系统日志配置
            "DLOG_LEVEL"     => array("debug","run","error","fatal"), //后台程序日志级别
            "LOG_FILE_SIZE"  => 1048576,
        ];
        config('wxconf',$wxconf);
    }

}

<?php
return array(
	//'配置项'=>'配置值'
       //(1) 测试用的服务号
       "APPID"          => "wxe01997548610a9dc",
       "APPSECRET"      => "7da6990ed964eb5ab88aa2f651b07a69",
      
       //(2)系统日志配置
       "DLOG_DIR"       => "./log/",     //后台程序日志存放的目录
       "DLOG_LEVEL"     => array("debug","run","error","fatal"), //后台程序日志级别
       "LOG_FILE_SIZE"  => 1048576,

       //(3)数据库的配置
       "DB_TYPE"       => "mysqli",
       "DB_HOST"       => "", //设置的为212的外网的IP地址
       "DB_NAME"       => "",
       "DB_USER"       => "",
       "DB_PWD"        => "",
       "DB_PORT"       => "",
       "DB_PREFIX"     => "t_",
       "DB_CHARSET"    => "utf8mb4"

);

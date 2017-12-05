<?php
/**
 * 记录系统日志
 * @param string $log_content 日志内容
 * @param string $log_level 日志级别      debug, run, error, fatal
 * @param string $log_name 日志文件名前缀，可不输入
 * @日志文件名 = $log_name + ".年-月-日" + .log 例: abc.2012-03-19.log
 * @return null
 * @author LEO 2012-03-19
 */
function DLOG($log_content = '', $log_level = '', $log_name = '')
{
    if (empty($log_level) || empty($log_content)) {
        return;
    }

    if (!in_array($log_level, config('wxconf.DLOG_LEVEL'))){
        return;
    }


    if (empty($log_name) && $log_level == 'run'){
        $log_name = 'run';
    }

    $log_file = LOG_PATH.$log_name.DS.date('Ym').DS;// 文件路径
    $log_file_name = date('d').".log";// 文件名

    //默认每行日志必写的内容，统一在这里处理
    // ZHANGXI 2015/1/7 for regular
    $time = sprintf("%8s.%03d", date('H:i:s'), floor(microtime() * 1000));
    $ip = sprintf("%15s", request()->ip());

    $id = "MIS";

    $path_arr = explode("/", $_SERVER['PATH_INFO']);

    $content_prefix = "[ " . $time . " " . $ip . " " . $id . " " . $log_level . " " . $path_arr[0] . " " . request()->action() . " ] ";

    if(!is_dir($log_file)){
        mkdir($log_file,0777,true);
    }

    $fp = fopen($log_file.$log_file_name, 'a+');
    fwrite($fp, $content_prefix . $log_content . " [" . getmypid() . "]\n");
    fclose($fp);
    return;
}

//get请求
function getHttp($url){
    $ch=curl_init();
    //设置传输地址
    curl_setopt($ch, CURLOPT_URL, $url);
    //设置以文件流形式输出
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //接收返回数据
    $data=curl_exec($ch);
    curl_close($ch);
    $jsonInfo=json_decode($data,true);
    return $jsonInfo;
}

//post请求
function postHttp($url,$json){
    $ch=curl_init();
    //设置传输地址
    curl_setopt($ch, CURLOPT_URL, $url);
    //设置以文件流形式输出
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //设置已post方式请求
    curl_setopt($ch, CURLOPT_POST, 1);
    //设置post文件
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    $data=curl_exec($ch);
    curl_close($ch);
    $jsonInfo=json_decode($data,true);
    return $jsonInfo;
}








?>
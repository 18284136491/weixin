<?php

namespace app\wechat\Controller;

use think\Controller;

/*
 *微信的入口文件
 */

class Wechat extends Controller
{
    protected $User;     //微信用户对象
    protected $app_id;
    protected $secret;

    /**
     * _initialize [构造函数]
     * @author dear
     * @return void
     */
    public function _initialize()
    {
        $this->app_id = config('wxconf.APPID');
        $this->secret = config('wxconf.APPSECRET');
    }

    /**
     * wechat [微信入口]
     * @author dear
     * @return void
     */
    public function wechat()
    {
        DLOG("微信入口记录的时间", "run", "caodi");
        if ($_GET['echostr'] != NULL) {
            echo $_GET['echostr'];
            exit;
        }
        //微信只会在第一次在URL中带echostr参数，以后就不会带这个参数了
        if ($this->checkSignature()) { //success!
            $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
            //extract post data
            if (!empty($postStr)) {
                libxml_disable_entity_loader(true);
                $postObj = simplexml_load_string($postStr, "SimpleXMLElement", LIBXML_NOCDATA);
                $this->User = $postObj;
//                $this->$User = $postObj;
                //根据消息类型将信息分发
                $this->route($postObj);
                //exit;

                //以下为测试用的
                $toUsername = $postObj->ToUserName;
                $fromUsername = $postObj->FromUserName;
                $keyword = trim($postObj->Content);
                $msyType = trim($postObj->MsgType); //消息类型
                $event = trim($postObj->Event); //事件类型
                $time = time();
                $result = json_encode($postObj);
                DLOG("消息的参数" . $result, "run", "caodi");
                $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";
                if ($event == "subscribe") {
                    $msgType = "text";
                    $contentStr = date("Y-m-d H:i:s", time());
                    $contentStr = '欢迎关注dear的公众号';
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                }
            }
        } else {
            echo "error";
        }
    }

    /**
     * checkSignature [wechat身份验证]
     * @author dear
     * @return bool* @return bool
     */
    public function checkSignature()
    {
        //you must define TOKEN by yourself
        if (!config('wxconf.TOKEN')) {
            throw new Exception("TOKEN is not defined!");
        }
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = config('wxconf.TOKEN');
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * route [根据微信的消息类型来进行的分发]
     * @author dear
     * @param $postObj
     * @return void
     */
    public function route($postObj)
    {
        $msgType = trim($postObj->MsgType);
        DLOG("mygtype=" . $msgType, "run", "caodi");
        switch ($msgType) {
            //(1)接受的为消息推送
            case "text":
                $this->reponse_text($postObj);
                break;
            case "image":
                $this->reponse_image($postObj);
                break;
            case "voice":
                $this->reponse_voice($postObj);
                break;
            //(2)接受的为事件推送
            case "event":
                $event = $postObj->Event;
                DLOG("event=" . $event, "run", "caodi");
                switch ($event) {
                    case "subscribe":
                        $this->subscribe($postObj);
                        break;
                    case "unsubscribe":
                        $this->unsubscribe($postObj);
                        break;
                    //自定义菜单的事件功能
                }

        }
    }

    /**
     * subscribe [微信用户关注微信号事件(获取用户的基本信息存入到用户表中去)]
     * @author dear
     * @param $postObj
     * @return void
     */
    public function subscribe($postObj)
    {
        $open_id = $postObj->FromUserName;
        $create_time = $postObj->CreateTime;
//        $UserDao = M("user");
        //(1)根据用户的open_id去 https://api.weixin.qq.com/cgi-bin/user/info?access_token=ACCESS_TOKEN&openid=OPENID&lang=zh_CN
        $access_token = self::get_token();
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $access_token . "&openid=" . $open_id . "&lang=zh_CN";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求保存的结果到字符串还是输出在屏幕上，非0表示保存到字符串中
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //对认证来源的检查，0表示阻止对证书的合法性检查
        $result = curl_exec($ch);
        DLOG("result" . $result, "run", "caodi");
        curl_close($ch);
        $user_info = json_decode($result, true);

        /*//(2)将得到的用户信息保存到数据库中去
        $data = [];
        $data['user_nick'] = $user_info['nickname'];
        $user_info['sex'] = $user_info['sex'] == 0 ? 1 : $user_info['sex']; //将性别为0的转化为默认的男性
        $data['user_sex'] = $user_info['sex'];
        $data['user_avatar'] = $user_info['headimgurl'];
        $data['user_type'] = 1;//用户类型 1-普通用户 2-助理
        $open_id = json_decode($open_id, true);
        $data['wx_open_id'] = $user_info['openid'];
        $data['user_app_version'] = "wechat9.0";
        $data['user_platform'] = "wechat";  //当前使用的设备平台
        $data['user_create_time'] = date("Y-m-d H:i:s", time());
        print_r($data);die;

        $result = $UserDao->add($data);
        DLOG("sql= " . $UserDao->getlastsql(), "run", "caodi");
        if ($result === false) {
            DLOG("数据库插入失败", "run", "caodi");exit;
        }*/
    }

    /**
     * create_menu [自定义菜单的生成]
     * @author dear
     * @return void
     */
    public function create_menu()
    {
        $data = config('menu_conf');
        $access_token = self::get_token();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $access_token;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        var_dump($result);
        exit;
    }

    /**
     * my_task [通过OAuth2.0的网页授权(自定义菜单中，获取用户的openID同时进入我的任务页)]
     * @author dear
     * @return void
     */
    public function my_task()
    {
        $code = $_GET['code'];
        $oprn_id = $this->code_to_openID($code);
        var_dump($code);
        echo "caodi" . "<br>";
        echo "<center><h1>{$open_id}</h1></center>";
    }

    /**
     * code_to_openID [由OAuth2.0获取到的code转化成用户的openID]
     * @author dear
     * @param $code
     * @return mixed* @return mixed
     */
    public function code_to_openID($code)
    {
        if (empty($code) == true) {
            DLOG("获取的code为空", "run", "caodi");
            exit;
        }
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $this->app_id . "&secret=" . $this->secret . "&code=" . $code . "&grant_type=authorization_code";
        $user_info = getHttp($url);
        $open_id = $user_info['openid'];
        return $open_id;
    }

    public function get_token()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->app_id."&secret=".$this->secret;
        $res = getHttp($url);
        return $res['access_token'];
    }


}

?>

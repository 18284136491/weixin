<?php 
namespace app\wechat\Controller;

use think\Controller;

// 微信页面
class Index extends Controller {
    protected $app_id;  //微信账号id
    protected $secret;  //微信公众号的密钥
    
    /*通用方法，此类的入口文件
     *author:caodi
     *date:2015-09-28
     */
    public function _initialize() {
        $this->app_id = config('wxconf.APPID');
        $this->secret = config('wxconf.APPSECRET');
    }

    public function index()
    {
    }

    /*微信页面的入口文件(我的页面)
     *author:caodi
     *date:2015-09-28
     */
    public function my_info() {
        $this->display("Index/index");
    }
}
?>
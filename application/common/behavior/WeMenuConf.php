<?php
namespace app\Common\behavior;

class WeMenuConf
{
    public function run()
    {
        $menu_conf = '{
            "button":[
                {    
                    "type":"click",
                    "name":"今日歌曲",
                    "key":"V1001_TODAY_MUSIC"
                },
                {
                    "type":"click",
                    "name":"歌手简介",
                    "key":"V1001_TODAY_SINGER"
                },
                {
                    "name":"菜单",
                    "sub_button":[
                        {    
                            "type":"view",
                            "name":"我的任务",
                            "url":"https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx66808704f67b0f0a&redirect_uri=http://mi.ai.la/Wechat/my_task&response_type=code&scope=snsapi_base&state=1#wechat_redirect"
                        },
                        {
                            "type":"view",
                            "name":"我的账户",
                            "url":"http://v.qq.com/"
                        },
                        {
                            "type":"click",
                            "name":"联系我们",
                            "key":"connect"
                        }
                    ]
                }
            ]
        }';

        config('menu_conf',$menu_conf);
    }
}
?>

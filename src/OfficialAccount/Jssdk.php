<?php
/**
 * Author: Hackbee
 */

namespace LightWechat\OfficialAccount;


trait Jssdk
{
    /**
     * 获取ticket。 jsapi_ticket是公众号用于调用微信JS接口的临时票据
     * 文档 https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421141115
     * @param string $type ticket类型（jsapi,wx_card）
     * @return bool
     */
    public function getTicket($type = 'jsapi')
    {
        $key = 'weixin_ticket_'.$type;
        $ticket = $this->getCache()->get($key);
        if (!empty($ticket)) {
            return $ticket;
        }

        if (!$access_token = $this->getAccessToken()) {
            return false;
        }

        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$access_token}&type={$type}";
        $return = $this->requestAndCheck($url, 'GET');
        if ($return === false) {
            return false;
        }

        $this->getCache()->set($key, $return['ticket'], 7200 -20);

        return $return['ticket'];
    }

    /**
     * 签名
     * @param string $url
     * @return array|bool
     */
    public function getSignPackage($url = '')
    {
        $ticket = $this->getTicket();
        if ($ticket === false) {
            return false;
        }

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = $url ?: $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);

        $signPackage = [
            "appId" => $this->options['appid'],
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url,
            "rawString" => $string,
            "signature" => $signature
        ];
        return $signPackage;
    }

    /**
     * 随机字符串
     * @param int $length
     * @return string
     */
    private function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 创建临时二维码
     * @param int $expire 过期时间，单位秒，最大30天，即2592000秒
     * @param int $scene_id 场景id，用户自定义，目前支持1-100000
     * @return boolean
     */
    public function createTempQrcode($expire, $scene_id)
    {
        if (!$access_token = $this->getAccessToken()) {
            return false;
        }

        $post = $this->toJson([
            'expire_seconds' => $expire,
            'action_name'    => 'QR_SCENE',
            'action_info'    => [
                'scene' => [
                    'scene_id' => $scene_id
                ]
            ]
        ]);

        $url ="https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={$access_token}";
        $return = $this->requestAndCheck($url, 'POST', $post);
        if ($return === false) {
            return false;
        }

//        返回数据格式：
//        {
//            "ticket":"gQH47joAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2taZ2Z3TVRtNzJXV1Brb3ZhYmJJAAIEZ23sUwMEmm3sUw==",
//            "expire_seconds":60,
//            "url":"http:\/\/weixin.qq.com\/q\/kZgfwMTm72WWPkovabbI"
//        }

        return $return;
    }
}
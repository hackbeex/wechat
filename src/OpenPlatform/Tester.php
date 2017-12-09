<?php
/**
 * Author: Hackbee
 */

namespace LightWechat\OpenPlatform;


trait Tester
{
    /**
     * 绑定微信用户为小程序体验者
     * 详见：https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1489140588_nVUgx&token=&lang=zh_CN
     * @param string $wechatId 微信号
     * @return boolean
     */
    public function bindTester($wechatId)
    {
        if ( ! $accessToken = $this->getAccessToken()) {
            return false;
        }

        $url ="https://api.weixin.qq.com/wxa/bind_tester?access_token={$accessToken}";
        $post = $this->toJson([
            'wechatid' => $wechatId,
        ]);

        $wxdata = $this->requestAndCheck($url, 'POST', $post);
        return $wxdata !== false;
    }

    /**
     * 解绑体验者
     * @param $wechatId
     * @return bool
     */
    public function unbindTester($wechatId)
    {
        if ( ! $accessToken = $this->getAccessToken()) {
            return false;
        }

        $url ="https://api.weixin.qq.com/wxa/unbind_tester?access_token={$accessToken}";
        $post = $this->toJson([
            'wechatid' => $wechatId,
        ]);

        $wxdata = $this->requestAndCheck($url, 'POST', $post);
        return $wxdata !== false;
    }
}
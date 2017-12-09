<?php
/**
 * Author: Hackbee
 */

namespace LightWechat;

use LightWechat\Core\WxCommon;
use LightWechat\OfficialAccount\Menu;
use LightWechat\OfficialAccount\User;
use LightWechat\OfficialAccount\Event;
use LightWechat\OfficialAccount\Jssdk;
use LightWechat\OfficialAccount\Message;
use LightWechat\OfficialAccount\Material;
use LightWechat\OfficialAccount\TemplateMessage;


class OfficialAccount extends WxCommon
{
    use Menu, User, Event, Jssdk, Message, Material, TemplateMessage;

    // 事件类型
    const EVENT_ALL = 0;        // 有事件就处理
    const EVENT_TEXT = 1;       // 文本输入事件
    const EVENT_SUBSCRIBE = 2;  // 关注事件
    const EVENT_UNSUBSCRIBE = 3;// 取消关注事件
    const EVENT_SCAN = 4;       // 已关注的扫描二维码事件
    const EVENT_LOCATION = 5;   // 上报二维码时间
    const EVENT_CLICK = 6;      // 点击菜单事件
    const EVENT_VIEW = 7;       // 点击菜单跳转链接事件

    /**
     * 微信公众号配置
     * @var array
     * [
     *   'appid' => '',
     *   'appsecret' => ''
     * ]
     */
    protected $options = [];

    public function __construct($options)
    {
        parent::__construct();

        $options && $this->options = $options;
    }

    /**
     * 获取access_token
     * @return string
     */
    public function getAccessToken()
    {
        $key = $this->options['appid'].':access_token';
        $accessToken = $this->getCache()->get($key);
        if ($accessToken) {
           return $accessToken;
        }

        $gets = http_build_query([
            'grant_type' => 'client_credential',
            'appid' => $this->options['appid'],
            'secret' => $this->options['appsecret'],
        ]);

        $url = "https://api.weixin.qq.com/cgi-bin/token?{$gets}";
        $return = $this->requestAndCheck($url);
        if ( ! isset($return['access_token'])) {
            $this->getCache()->delete($key);
            return false;
        }

        $this->getCache()->set($key, $return['access_token'], 7200 - 20);
        
        return $return['access_token'];
    }
}
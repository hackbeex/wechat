<?php
/**
 * Author: Hackbee
 */

namespace LightWechat;

use LightWechat\Core\WxCommon;
use LightWechat\MiniProgram\Domain;
use LightWechat\OpenPlatform\Tester;
use LightWechat\MiniProgram\CodeManage;


class MiniProgram extends WxCommon
{
    use Domain, Tester, CodeManage;

    /**
     * @var array 小程序配置
     * [
     *   'appid'
     *   ''
     * ]
     */
    protected $options = [];

    /**
     * @var OpenPlatform 开放平台实例
     */
    protected $openPlatform;

    /**
     * 获取实例
     * @param $options array
     * @param $openPlatform OpenPlatform
     */
    public function __construct($options, $openPlatform)
    {
        parent::__construct();

        $this->options = $options;
        $this->openPlatform = $openPlatform;
    }
    
    /**
     * 获取授权者的authorizer_access_token
     * @return boolean
     */
    public function getAccessToken()
    {
        $key = $this->options['appid'].':access_token';
        $accessToken = $this->getCache()->get($key);
        if ($accessToken) {
            return $accessToken;
        }

        $return = $this->openPlatform->getAuthorizerToken($this->options['appid'], $this->options['refresh_token']);
        if ($return === false) {
            $this->setError($this->openPlatform->getError());
            $this->getCache()->delete($key);
            return false;
        }

        $this->getCache()->set($key, $return['authorizer_access_token'], $return['expires_in'] - 20);
        $this->getCache()->set($this->options['appid'].':refresh_token', $return['authorizer_refresh_token']);
        
        return $return['authorizer_access_token'];
    }

    /**
     * 获取小程序session信息
     * @param string $code 登录码
     * @return array|bool
     */
    public function getSessionInfo($code)
    {
        $openOptions = $this->openPlatform->getOptions();
        $url = 'https://api.weixin.qq.com/sns/component/jscode2session';
        $fields = [
            'appid' => $this->options['appid'],
            'js_code' => $code,
            'grant_type' => 'authorization_code',
            'component_appid' => $openOptions['appid'],
            'component_access_token' => $this->openPlatform->getComponentAccessToken(),
        ];

        $wxdata = $this->requestAndCheck($url, 'GET', $fields);
        if ($wxdata === false) {
            return false;
        }
        return $wxdata;
    }

    /**
     * 获取授权用户的详细信息
     * @return boolean | array
     */
    public function getAuthUserInfo()
    {
        $return = $this->openPlatform->getAuthorizerInfo($this->options['appid']);
        if ($return === false) {
            $this->setError($this->openPlatform->getError());
        }

        return $return;
    }
}
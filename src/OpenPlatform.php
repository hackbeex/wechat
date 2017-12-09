<?php
/**
 * Author: Hackbee
 */

namespace LightWechat;

use LightWechat\Core\WxCommon;
use LightWechat\OpenPlatform\Event;
use LightWechat\OpenPlatform\Authorizer;

/**
 * 微信小程序第三方平台操作类
 * 单例模式
 */
class OpenPlatform extends WxCommon
{
    use Event, Authorizer;

    /**
     * @var array 开放平台配置
     * [
     *   'appid'
     *   'appsecret'
     *   'verify_ticket'
     *   'encoding_aes_key'
     *   'verify_token'
     * ]
     */
    protected $options = [];

    /**
     * @var self 单例
     */
    static private $instance = null;


    protected function __construct($options = null)
    {
        parent::__construct();

        $options && $this->options = $options;
    }
    
    /**
     * 获取实例
     * @param array $options
     * @return self
     */
    static public function getInstance($options = null)
    {
        if (!self::$instance) {
            self::$instance = new self($options);
        }

        $options && self::$instance->options = $options;
        return self::$instance;
    }
    
    /**
     * 获取第三方平台令牌（组件方令牌）
     * 详见：https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1453779503&token=&lang=zh_CN
     * @return string
     */
    public function getComponentAccessToken()
    {
        if (empty($this->options)) {
            $this->setError("第三方平台信息未配置");
            return false;
        }

        $accessToken = $this->cache->get('component_access_token');
        if ($accessToken) {
            return $accessToken;
        }
        
        $post = $this->toJson([
            'component_appid'       => $this->options['appid'] ,
            'component_appsecret'   => $this->options['appsecret'],
            'component_verify_ticket' => $this->options['verify_ticket']
        ]);
        $url = "https://api.weixin.qq.com/cgi-bin/component/api_component_token";
        $return = $this->requestAndCheck($url, 'POST', $post);
        if ($return === false) {
            $this->cache->delete('component_access_token');
            return false;
        }

        $this->cache->set('component_access_token', $return['component_access_token'], 7200 - 20);
        
        return $return['component_access_token'];
    }
    
    /**
     * 获取预授权码pre_auth_code
     * 详见：https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1453779503&token=&lang=zh_CN
     * @return mixed 预授权码
     */
    public function getPreAuthCode()
    {
        $accessToken = $this->getComponentAccessToken();
        if (!$accessToken) {
            return false;
        }
        
        $url ="https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token={$accessToken}";        
        $post = $this->toJson([
            'component_appid' => $this->options['appid'],
        ]);
        
        $wxdata = $this->requestAndCheck($url, 'POST', $post);
        if ($wxdata === false) {
            return false;
        }
        
        return $wxdata['pre_auth_code'];
    }
    
    /**
     * 使用授权码换取公众号或小程序的接口调用凭据和授权信息
     * 详见：https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1453779503&token=&lang=zh_CN
     * @param string $authCode 授权码
     * @return mixed 授权数组
     */
    public function getAuthInfo($authCode)
    {
        $accessToken = $this->getComponentAccessToken();
        if (!$accessToken) {
            return false;
        }
        
        $url ="https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token={$accessToken}";        
        $post = $this->toJson([
            'component_appid'    => $this->options['appid'],
            'authorization_code' => $authCode
        ]);
        
        $wxdata = $this->requestAndCheck($url, 'POST', $post);
        if ($wxdata === false) {
            return false;
        }
        
        //返回数据结构
        //{ 
        //    "authorization_info": {
        //        "authorizer_appid": "wxf8b4f85f3a794e77", 
        //        "authorizer_access_token": "QXjUqNqfYVH0yBE1iI_7vuN_9gQbpjfK7hYwJ3P7xOa88a89-Aga5x1NMYJyB8G2yKt1KCl0nPC3W9GJzw0Zzq_dBxc8pxIGUNi_bFes0qM", 
        //        "expires_in": 7200, 
        //        "authorizer_refresh_token": "dTo-YCXPL4llX-u1W1pPpnp8Hgm4wpJtlR6iV0doKdY", 
        //        "func_info": [
        //            {
        //                "funcscope_category": {
        //                    "id": 1
        //                }
        //            }
        //        ]
        //    }
        //}
        return $wxdata['authorization_info'];
    }
    
    /**
     * 获取授权的页面url
     * @param $redirectUrl string 回调url
     * @return string
     */
    public function getAuthUrl($redirectUrl)
    {
        $preAuthCode = $this->getPreAuthCode();
        if ($preAuthCode === false) {
            return false;
        }

        $params = http_build_query([
            'component_appid' => $this->options['appid'],
            'pre_auth_code' => urlencode($preAuthCode),
            'redirect_uri' => $redirectUrl,
        ]);

        return  'https://mp.weixin.qq.com/cgi-bin/componentloginpage?'.$params;
    }
}
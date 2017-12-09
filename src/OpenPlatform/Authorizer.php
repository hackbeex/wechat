<?php
/**
 * Author: Hackbee
 */

namespace LightWechat\OpenPlatform;


trait Authorizer
{
    /**
     * 获取（刷新）授权公众号或小程序的接口调用凭据（令牌）
     * 详见：https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1453779503&token=&lang=zh_CN
     * @param string $authorizerAppid  授权方appid
     * @param string $refreshTokenValue 授权方的刷新令牌
     * @return mixed 授权数组
     */
    public function getAuthorizerToken($authorizerAppid, $refreshTokenValue)
    {
        $accessToken = $this->getComponentAccessToken();
        if (!$accessToken) {
            return false;
        }

        $url ="https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token={$accessToken}";
        $post = $this->toJson([
            'component_appid'   => $this->options['appid'],
            'authorizer_appid'  => $authorizerAppid,
            'authorizer_refresh_token' => $refreshTokenValue,
        ]);

        $wxdata = $this->requestAndCheck($url, 'POST', $post);
        if ($wxdata === false) {
            return false;
        }

        //返回数据结构
        //{
        //    "authorizer_access_token": "aaUl5s6kAByLwgV0BhXNuIFFUqfrR8vTATsoSHukcIGqJgrc4KmMJ-JlKoC_-NKCLBvuU1cWPv4vDcLN8Z0pn5I45mpATruU0b51hzeT1f8",
        //    "expires_in": 7200,
        //    "authorizer_refresh_token": "BstnRqgTJBXb9N2aJq6L5hzfJwP406tpfahQeLNxX0w"
        //}
        return $wxdata;
    }

    /**
     * 获取授权方的帐号基本信息
     * @param string $authorizerAppid 授权公众号或小程序的appid
     * @return mixed
     */
    public function getAuthorizerInfo($authorizerAppid)
    {
        $accessToken = $this->getComponentAccessToken();
        if (!$accessToken) {
            return false;
        }

        $url ="https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token={$accessToken}";
        $post = $this->toJson([
            'component_appid'   => $this->options['appid'],
            'authorizer_appid'  => $authorizerAppid,
        ]);

        $wxdata = $this->requestAndCheck($url, 'POST', $post);
        if ($wxdata === false) {
            return false;
        }

        //返回数据结构（小程序）
        //{
        //    "authorizer_info": {
        //        "nick_name": "微信SDK Demo Special",
        //        "head_img": "http://wx.qlogo.cn/mmopen/GPy",
        //        "service_type_info": { "id": 2 },
        //        "verify_type_info": { "id": 0 },
        //        "user_name":"gh_eb5e3a772040",
        //        "principal_name":"腾讯计算机系统有限公司",
        //        "business_info": {"open_store": 0, "open_scan": 0, "open_pay": 0, "open_card": 0, "open_shake": 0},
        //        "qrcode_url":"URL",
        //        "signature": "时间的水缓缓流去",
        //        "MiniProgramInfo": {
        //            "network": {
        //                "RequestDomain":["https://www.qq.com","https://www.qq.com"],
        //                "WsRequestDomain":["wss://www.qq.com","wss://www.qq.com"],
        //                "UploadDomain":["https://www.qq.com","https://www.qq.com"],
        //                "DownloadDomain":["https://www.qq.com","https://www.qq.com"],
        //            },
        //            "categories":[{"first":"资讯","second":"文娱"},{"first":"工具","second":"天气"}],
        //            "visit_status": 0,
        //        }
        //    },
        //    "authorization_info": {
        //        "appid": "wxf8b4f85f3a794e77",
        //        "func_info": [
        //            { "funcscope_category": { "id": 17 } },
        //        ]
        //    }
        //}

        //返回数据结构（公众号）
        //{
        //    "authorizer_info": {
        //        "nick_name": "微信SDK Demo Special",
        //        "head_img": "http://wx.qlogo.cn/mmopen/GPy",
        //        "service_type_info": { "id": 2 },
        //        "verify_type_info": { "id": 0 },
        //        "user_name":"gh_eb5e3a772040",
        //        "principal_name":"腾讯计算机系统有限公司",
        //        "business_info": {"open_store": 0, "open_scan": 0, "open_pay": 0, "open_card": 0, "open_shake": 0},
        //        "alias":"paytest01"
        //        "qrcode_url":"URL",
        //    },
        //    "authorization_info": {
        //        "appid": "wxf8b4f85f3a794e77",
        //        "func_info": [
        //            { "funcscope_category": { "id": 1 } },
        //        ]
        //    }
        //}
        return $wxdata;
    }

    /**
     * 获取授权方的选项设置信息
     * 详见：https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1453779503&token=&lang=zh_CN
     * @param string $authorizerAppid 授权公众号或小程序的appid
     * @param string $optionName 选项名称
     * @return boolean
     */
    public function getAuthorizerOption($authorizerAppid, $optionName)
    {
        $accessToken = $this->getComponentAccessToken();
        if (!$accessToken) {
            return false;
        }

        $url ="https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_option?component_access_token={$accessToken}";
        $post = $this->toJson([
            'component_appid'   => $this->options['appid'],
            'authorizer_appid'  => $authorizerAppid,
            "option_name"       => $optionName
        ]);

        $wxdata = $this->requestAndCheck($url, 'POST', $post);
        if ($wxdata === false) {
            return false;
        }

        //返回数据结构
        //{
        //    "authorizer_appid":"wx7bc5ba58cabd00f4",
        //    "option_name":"voice_recognize", //选项名称
        //    "option_value":"1" //选项值
        //}
        return $wxdata;
    }

    /**
     * 设置授权方的选项信息
     * 详见：https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1453779503&token=&lang=zh_CN
     * @param string $authorizerAppid 授权公众号或小程序的appid
     * @param string $optionName 选项名称
     * @param string $optionValue 设置的选项值
     * @return boolean
     */
    public function setAuthorizerOption($authorizerAppid, $optionName, $optionValue)
    {
        $accessToken = $this->getComponentAccessToken();
        if (!$accessToken) {
            return false;
        }

        $url ="https://api.weixin.qq.com/cgi-bin/component/api_set_authorizer_option?component_access_token={$accessToken}";
        $post = $this->toJson([
            'component_appid'   => $this->options['appid'],
            'authorizer_appid'  => $authorizerAppid,
            "option_name"       => $optionName,
            "option_value"      => $optionValue
        ]);

        $wxdata = $this->requestAndCheck($url, 'POST', $post);
        return $wxdata !== false;
    }

    /**
     * 获取授权的用户列表
     * @param int $count 一次获取的数量，最大500
     * @param int $offset 获取数量的偏移值
     * @return bool|mixed|string
     */
    public function getAuthorizerList($count = 500, $offset = 0)
    {
        $accessToken = $this->getComponentAccessToken();
        if (!$accessToken) {
            return false;
        }

        $url ="https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_list?component_access_token={$accessToken}";
        $post = $this->toJson([
            'component_appid'   => $this->options['appid'],
            'offset'  => $offset,
            "count"   => $count
        ]);

        $wxdata = $this->requestAndCheck($url, 'POST', $post);
        if ($wxdata === false) {
            return false;
        }

        //返回数据结构
        //{
        //    "total_count":33,
        //    list:[
        //        {
        //          "authorizer_appid": "authorizer_appid_1",
        //          "refresh_token": "refresh_token_1",
        //          "auth_time": auth_time_1
        //        },
        //    ]
        //}
        return $wxdata;
    }
}
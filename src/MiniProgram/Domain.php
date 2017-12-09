<?php
/**
 * Author: Hackbee
 */

namespace LightWechat\MiniProgram;


trait Domain
{
    /**
     * 修改服务器地址
     * 详看：https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1489138143_WPbOO&token=&lang=zh_CN
     * @param string $action add添加, delete删除, set覆盖
     * @param array $domains
     *              requestDomain request合法域名
     *              wsrequestDomain socket合法域名
     *              uploadDomain uploadFile合法域名
     *              downloadDomain downloadFile合法域名
     * @return boolean
     */
    public function modifyDomain($action, $domains)
    {
        if ( ! $accessToken = $this->getAccessToken()) {
            return false;
        }

        $url ="https://api.weixin.qq.com/wxa/modify_domain?access_token={$accessToken}";
        $post = $this->toJson([
            'action'            => $action,
            'requestdomain'     => $domains['requestdomain'],
            'wsrequestdomain'   => $domains['wsrequestdomain'],
            'uploaddomain'      => $domains['uploaddomain'],
            'downloaddomain'    => $domains['downloaddomain'],
        ]);

        $wxdata = $this->requestAndCheck($url, 'POST', $post);
        return $wxdata !== false;
    }

    /**
     * 获取服务器地址
     * 详看：https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1489138143_WPbOO&token=&lang=zh_CN
     * @return mixed
     */
    public function getDomain()
    {
        if ( ! $accessToken = $this->getAccessToken()) {
            return false;
        }

        $url ="https://api.weixin.qq.com/wxa/modify_domain?access_token={$accessToken}";
        $post = $this->toJson([
            'action' => 'get',
        ]);

        $wxdata = $this->requestAndCheck($url, 'POST', $post);
        if ($wxdata === false) {
            return false;
        }
        return [
            'requestdomain'   => $wxdata['requestdomain'],
            'wsrequestdomain' => $wxdata['wsrequestdomain'],
            'uploaddomain'    => $wxdata['uploaddomain'],
            'downloaddomain'  => $wxdata['downloaddomain'],
        ];
    }
}
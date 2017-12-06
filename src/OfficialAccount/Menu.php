<?php
/**
 * Author: Hackbee
 */

namespace LightWechat\OfficialAccount;


trait Menu
{
    /**
     * 创建自定义菜单
     * 文档：https://mp.weixin.qq.com/wiki?action=doc&id=mp1421141013
     * @param $data array
     * @return bool
     */
    public function createMenu($data)
    {
        if (!$access_token = $this->getAccessToken()) {
            return false;
        }

        $post = $this->toJson($data);
        $url ="https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$access_token}";
        $return = $this->requestAndCheck($url, 'POST', $post);
        if ($return === false) {
            return false;
        }
        return true;
    }
}
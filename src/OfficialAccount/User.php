<?php
/**
 * Author: Hackbee
 */

namespace LightWechat\OfficialAccount;


trait User
{
    protected $tagsMap = null;    // 粉丝标签映射

    /**
     * 获取粉丝详细信息
     * @param string $openid
     * @param string $access_token 如果为null，自动获取
     * @return array|bool
     */
    public function getUserInfo($openid, $access_token = null)
    {
        if (null === $access_token) {
            if (!$access_token = $this->getAccessToken()) {
                return false;
            }
        }

        $url ="https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$openid}&lang=zh_CN";
        $return = $this->requestAndCheck($url, 'GET');
        if ($return === false) {
            return false;
        }

        /* $wxdata[]元素：
         * subscribe	用户是否订阅该公众号标识，值为0时，代表此用户没有关注该公众号，拉取不到其余信息。
         * openid	用户的标识，对当前公众号唯一
         * nickname	用户的昵称
         * sex	用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
         * city	用户所在城市
         * country	用户所在国家
         * province	用户所在省份
         * language	用户的语言，简体中文为zh_CN
         * headimgurl	用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空。若用户更换头像，原有头像URL将失效。
         * subscribe_time	用户关注时间，为时间戳。如果用户曾多次关注，则取最后关注时间
         * unionid	只有在用户将公众号绑定到微信开放平台帐号后，才会出现该字段。
         * remark	公众号运营者对粉丝的备注，公众号运营者可在微信公众平台用户管理界面对粉丝添加备注
         * groupid	用户所在的分组ID（兼容旧的用户分组接口）
         * tagid_list	用户被打上的标签ID列表
         */
        $return['sex_name'] = $this->sexName($return['sex']);
        return $return;
    }

    /**
     * sex_id 用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
     */
    public function sexName($sex_id)
    {
        if ($sex_id == 1) {
            return '男';
        } else if ($sex_id == 2) {
            return '女';
        }
        return '未知';
    }

    /**
     * 获取粉丝标签
     * @return mixed
     */
    public function getAllUserTags()
    {
        if (!$access_token = $this->getAccessToken()) {
            return false;
        }

        $url = "https://api.weixin.qq.com/cgi-bin/tags/get?access_token={$access_token}";
        $return = $this->requestAndCheck($url, 'GET');
        if ($return === false) {
            return false;
        }

        //$wxdata数据样例：{"tags":[{"id":1,"name":"每天一罐可乐星人","count":0/*此标签下粉丝数*/}, ...]}
        return $return['tags'];
    }

    /**
     * 获取所有用户标签
     * @return array|bool
     */
    public function getAllUserTagsMap()
    {
        if ($this->tagsMap !== null) {
            return $this->tagsMap;
        }

        $user_tags = $this->getAllUserTags();
        if ($user_tags === false) {
            return false;
        }

        $this->tagsMap = [];
        foreach ($user_tags as $tag) {
            $this->tagsMap[$tag['id']] = $this->tagsMap[$tag['name']];
        }
        return $this->tagsMap;
    }

    /**
     * 获取粉丝标签名
     * @param array $tagid_list
     * @param array $tagsMap
     * @return array|bool
     */
    public function getUserTagNames($tagid_list)
    {
        if ($this->tagsMap === null) {
            $tagsMap = $this->getAllUserTagsMap();
            if ($tagsMap === false) {
                return false;
            }
            $this->tagsMap = $tagsMap;
        }

        $tag_names = [];
        foreach ($tagid_list as $tag) {
            $tag_names[] = $this->tagsMap[$tag];
        }
        return $tag_names;
    }

    /**
     * 获取粉丝id列表
     * @param string $next_openid 下一次拉取的起始id的前一个id
     * @return array|bool
     */
    public function getUserIdList($next_openid='')
    {
        if (!$access_token = $this->getAccessToken()) {
            return false;
        }

        $url ="https://api.weixin.qq.com/cgi-bin/user/get?access_token={$access_token}&next_openid={$next_openid}";//重头开始拉取，一次最多拉取10000个
        $return = $this->requestAndCheck($url, 'GET');
        if ($return === false) {
            return false;
        }

        //$list[]元素：
        //total	关注该公众账号的总用户数
        //count	拉取的OPENID个数，最大值为10000
        //data	列表数据，OPENID的列表
        //next_openid	拉取列表的最后一个用户的OPENID
        //样本数据：{"total":2,"count":2,"data":{"openid":["OPENID1","OPENID2"]},"next_openid":"NEXT_OPENID"}
        return $return;
    }

    /**
     * 设置粉丝备注
     */
    public function setUserRemark($openid, $remark)
    {
        if (!$access_token = $this->getAccessToken()) {
            return false;
        }

        $post = $this->toJson(['openid '=> $openid, 'remark' => $remark]);
        $url ="https://api.weixin.qq.com/cgi-bin/user/info/updateremark?access_token={$access_token}";
        $return = $this->requestAndCheck($url, 'POST', $post);
        if ($return === false) {
            return false;
        }

        return true;
    }
}
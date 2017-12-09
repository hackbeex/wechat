<?php
/**
 * Author: Hackbee
 */


namespace LightWechat\MiniProgram;


trait CodeManage
{

    /**
     * 为授权的小程序帐号上传小程序代码
     * 详见：https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1489140610_Uavc4&token=&lang=zh_CN
     * @param string $templateId 代码库中的代码模版ID
     * @param string|array $extCfg 第三方自定义的配置json
     * @param string $version 代码版本号，开发者可自定义
     * @param string $description 代码描述，开发者可自定义
     * @return boolean
     */
    public function commit($templateId, $extCfg, $version, $description)
    {
        if ( ! $accessToken = $this->getAccessToken()) {
            return false;
        }

        if (!is_string($extCfg)) {
            $extCfg = $this->toJson($extCfg);
        }

        $url ="https://api.weixin.qq.com/wxa/commit?access_token={$accessToken}";
        $post = $this->toJson([
            'template_id' => $templateId,
            'ext_json' => $extCfg, //需为string类型
            'user_version' => $version, //代码版本号
            'user_desc' => $description,
        ]);

        $wxdata = $this->requestAndCheck($url, 'POST', $post);
        return $wxdata !== false;
    }

    /**
     * 将第三方提交的代码包提交审核（仅供第三方开发者代小程序调用）
     * 详见：https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1489140610_Uavc4&token=&lang=zh_CN
     * @param array $itemList
     * 示例：[{
    "address":"page/logs/logs",//小程序的页面，可通过“获取小程序的第三方提交代码的页面配置”接口获得
    "tag":"学习 工作",          //小程序的标签，多个标签用空格分隔，标签不能多于10个，标签长度不超过20
    "first_class": "教育",      //一级类目名称，可通过“获取授权小程序帐号的可选类目”接口获得
    "second_class": "学历教育", //二级类目(同上)
    "third_class": "高等",      //三级类目(同上)
    "first_id":3,               //一级类目的ID，可通过“获取授权小程序帐号的可选类目”接口获得
    "second_id":4,              //二级类目的ID(同上)
    "third_id":5,               //三级类目的ID(同上)
    "title": "日志"             //小程序页面的标题,标题长度不超过32
    }]
     * @return mixed 审核编号
     */
    public function submitAudit($itemList)
    {
        if ( ! $accessToken = $this->getAccessToken()) {
            return false;
        }

        $url ="https://api.weixin.qq.com/wxa/submit_audit?access_token={$accessToken}";
        $post = $this->toJson([
            'item_list' => $itemList
        ]);

        $wxdata = $this->requestAndCheck($url, 'POST', $post);
        if ($wxdata === false) {
            return false;
        }

        return $wxdata['auditid'];
    }

    /**
     * 查询某个指定版本的审核状态（仅供第三方代小程序调用）
     * 详见：https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1489140610_Uavc4&token=&lang=zh_CN
     * @param string $auditId 提交审核时获得的审核id
     * @return mixed 审核结果数组
     */
    public function getAuditStatus($auditId)
    {
        if ( ! $accessToken = $this->getAccessToken()) {
            return false;
        }

        $url ="https://api.weixin.qq.com/wxa/get_auditstatus?access_token={$accessToken}";
        $post = $this->toJson([
            'auditid' => $auditId
        ]);

        $wxdata = $this->requestAndCheck($url, 'POST', $post);
        if ($wxdata === false) {
            return false;
        }

        return [
            'status' => $wxdata['status'],  //0为审核成功，1为审核失败，2为审核中
            'reason' => isset($wxdata['reason']) ? $wxdata['reason'] : '' //当status=1，审核被拒绝时，返回的拒绝原因
        ];
    }

    /**
     * 查询最新一次提交的审核状态（仅供第三方代小程序调用）
     * 详见：https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1489140610_Uavc4&token=&lang=zh_CN
     * @return mixed 审核结果数组
     */
    public function getLatestAuditStatus()
    {
        if ( ! $accessToken = $this->getAccessToken()) {
            return false;
        }

        $url ="https://api.weixin.qq.com/wxa/get_latest_auditstatus?access_token={$accessToken}";
        $wxdata = $this->requestAndCheck($url);
        if ($wxdata === false) {
            return false;
        }

        return [
            'auditid' => $wxdata['auditid'], //最新的审核ID
            'status' => $wxdata['status'],  //0为审核成功，1为审核失败，2为审核中
            'reason' => isset($wxdata['reason']) ? $wxdata['reason'] : '' //当status=1，审核被拒绝时，返回的拒绝原因
        ];
    }

    /**
     * 发布已通过审核的小程序（仅供第三方代小程序调用）
     * 详见：https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1489140610_Uavc4&token=&lang=zh_CN
     * @return boolean
     */
    public function release()
    {
        if ( ! $accessToken = $this->getAccessToken()) {
            return false;
        }

        $url ="https://api.weixin.qq.com/wxa/release?access_token={$accessToken}";
        $post = '{}'; //官方要求空的数据包

        $wxdata = $this->requestAndCheck($url, 'POST', $post);
        return $wxdata !== false;
    }

    /**
     * 修改小程序线上代码的可见状态（仅供第三方代小程序调用）
     * @param string $action 设置可访问状态，发布后默认可访问，1可见 0不可见
     * @return boolean
     */
    public function changeVisitStatus($action)
    {
        if ( ! $accessToken = $this->getAccessToken()) {
            return false;
        }

        $url ="https://api.weixin.qq.com/wxa/change_visitstatus?access_token={$accessToken}";
        $post = $this->toJson([
            'action' => $action ? 'open' : 'close'
        ]);

        $wxdata = $this->requestAndCheck($url, 'POST', $post);
        return $wxdata !== false;
    }

    /**
     * 获取体验小程序的体验二维码链接
     * 详见：https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1489140610_Uavc4&token=&lang=zh_CN
     * @return string
     */
    public function getTestQrcode()
    {
        if ( ! $accessToken = $this->getAccessToken()) {
            return false;
        }

        $url ="https://api.weixin.qq.com/wxa/get_qrcode?access_token={$accessToken}";
        $wxdata = $this->requestAndCheck($url, 'GET', [], false);

        return $wxdata;
    }

    /**
     * 获取授权小程序帐号的可选类目
     * 详见：https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1489140610_Uavc4&token=&lang=zh_CN
     * @return mixed
     */
    public function getCategory()
    {
        if ( ! $accessToken = $this->getAccessToken()) {
            return false;
        }

        $url ="https://api.weixin.qq.com/wxa/get_category?access_token={$accessToken}";

        $wxdata = $this->requestAndCheck($url);
        if ($wxdata === false) {
            return false;
        }

        //返回格式：如下：
        //[{
        //  "first_class":"教育", //一级类目名称
        //  "second_class":"学历教育",
        //  "third_class":"高等"
        //  "first_id":3, //一级类目的ID编号
        //  "second_id":4,
        //  "third_id":5,
        //}]
        return $wxdata['category_list'];
    }

    /**
     * 获取小程序的第三方提交代码的页面配置（仅供第三方开发者代小程序调用）
     * 详见：https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1489140610_Uavc4&token=&lang=zh_CN
     * @return mixed
     */
    public function getPage()
    {
        if ( ! $accessToken = $this->getAccessToken()) {
            return false;
        }

        $url ="https://api.weixin.qq.com/wxa/get_page?access_token={$accessToken}";

        $wxdata = $this->requestAndCheck($url);
        if ($wxdata === false) {
            return false;
        }

        return $wxdata['page_list'];
    }
}
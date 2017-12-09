<?php
/**
 * Author: Hackbee
 */

namespace LightWechat\OfficialAccount;


trait TemplateMessage
{
    /**
     * 获取用户所有模板消息
     * @return bool|mixed|string
     */
    public function getAllTemplateMsg()
    {
        if (!$access_token = $this->getAccessToken()) {
            return false;
        }

        $url ="https://api.weixin.qq.com/cgi-bin/material/get_all_private_template?access_token={$access_token}";
        $return = $this->requestAndCheck($url, 'GET');
        if ($return === false) {
            return false;
        }

        //返回数据格式：
        //{"template_list": [{
        //    "template_id": "iPk5sOIt5X_flOVKn5GrTFpncEYTojx6ddbt8WYoV5s",
        //    "title": "领取奖金提醒",
        //    "primary_industry": "IT科技",
        //    "deputy_industry": "互联网|电子商务",
        //    "content": "{ {result.DATA} }\n\n领奖金额:{ {withdrawMoney.DATA} }\n领奖  时间:{ {withdrawTime.DATA} }\n银行信息:{ {cardInfo.DATA} }\n到账时间:  { {arrivedTime.DATA} }\n{ {remark.DATA} }",
        //    "example": "您已提交领奖申请\n\n领奖金额：xxxx元\n领奖时间：2013-10-10 12:22:22\n银行信息：xx银行(尾号xxxx)\n到账时间：预计xxxxxxx\n\n预计将于xxxx到达您的银行卡"
        //}, ...]}
        return $return;
    }

    /**
     * 添加消息模板
     * @param $template_sn string 模板编号
     * @return bool|string 模板id
     */
    public function addTemplateMsg($template_sn)
    {
        if (!$access_token = $this->getAccessToken()) {
            return false;
        }

        $post = $this->toJson(['template_id_short' => $template_sn]);
        $url ="https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token={$access_token}";
        $return = $this->requestAndCheck($url, 'POST', $post);
        if ($return === false) {
            return false;
        }
        return $return['template_id'];
    }

    /**
     * 删除模板消息
     * @param $template_id string 模板id
     * @return bool
     */
    public function delTemplateMsg($template_id)
    {
        if (!$access_token = $this->getAccessToken()) {
            return false;
        }

        $post = $this->toJson(['template_id' => $template_id]);
        $url ="https://api.weixin.qq.com/cgi-bin/template/del_private_template?access_token={$access_token}";
        $return = $this->requestAndCheck($url, 'POST', $post);
        if ($return === false) {
            return false;
        }
        return true;
    }

    /**
     * 发送模板消息
     * @param $openid
     * @param $template_id
     * @param $url string 模板跳转链接
     * @param $data array 模板数据
     * @param $miniapp array 小程序数据
     * @return bool
     */
    public function sendTemplateMsg($openid, $template_id, $url, $data, $miniapp = [])
    {
        if (!$access_token = $this->getAccessToken()) {
            return false;
        }

        $post = $this->toJson([
            "touser" => $openid,
            "template_id" => $template_id,
            "url" => $url, //模板跳转链接

            "miniprogram" => $miniapp, //小程序跳转配置
            //[
            //    "appid" => "xiaochengxuappid12345",
            //    "pagepath" => "index?foo=bar"
            //],

            "data" => $data, //模板数据
            //[
            //    "first" =>  [
            //        "value" => "恭喜你购买成功！",
            //        "color" => "#173177"
            //    ],
            //    "keynote1" => [
            //        "value" => "巧克力",
            //        "color" => "#173177"
            //    ],
            //    "remark" => [
            //        "value" => "欢迎再次购买！",
            //        "color" => "#173177"
            //    ]
            //]
        ]);
        //注：url和miniprogram都是非必填字段，若都不传则模板无跳转；若都传，会优先跳转至小程序。
        //开发者可根据实际需要选择其中一种跳转方式即可。当用户的微信客户端版本不支持跳小程序时，将会跳转至url

        $url ="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}";
        $return = $this->requestAndCheck($url, 'POST', $post);
        if ($return === false) {
            return false;
        }
        return true;
    }
}
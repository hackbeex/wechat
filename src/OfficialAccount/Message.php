<?php
/**
 * Author: Hackbee
 */

namespace LightWechat\OfficialAccount;


trait Message
{
    /*
     * 向一个粉丝发送消息
     * 文档：https://mp.weixin.qq.com/wiki?action=doc&id=mp1421140547#2
     * @param $type string (text,news,image,voice,video,music,mpnews,wxcard)
     */
    public function sendMsgToOne($openid, $type, $content)
    {
        if (!$access_token = $this->getAccessToken()) {
            return false;
        }

        $data = [
            'touser' => $openid,
            'msgtype' => $type,
        ];

        if ($type == 'text') {
            $data[$type]['content'] = $content; //text
        } elseif (in_array($type, ['image', 'voice', 'mpnews'])) {
            $data[$type]['media_id'] = $content; //media_id
        } elseif ($type == 'wxcard') {
            $data[$type]['card_id'] = $content; //card_id
        } elseif ($type == 'news') {
            //$content = [{
            //     "title":"Happy Day",
            //     "description":"Is Really A Happy Day",
            //     "url":"URL",
            //     "picurl":"PIC_URL"
            //}, ...]
            $data[$type]['articles'] = $content;
        } elseif ($type == 'video') {
            //$content = {
            //    "media_id":"MEDIA_ID",
            //    "thumb_media_id":"MEDIA_ID",
            //    "title":"TITLE",
            //    "description":"DESCRIPTION"
            //}
            $data[$type] = $content;
        } elseif ($type == 'music') {
            //$content = {
            //    "title":"MUSIC_TITLE",
            //    "description":"MUSIC_DESCRIPTION",
            //    "musicurl":"MUSIC_URL",
            //    "hqmusicurl":"HQ_MUSIC_URL",
            //    "thumb_media_id":"THUMB_MEDIA_ID"
            //}
            $data[$type] = $content;
        }

        $post = $this->toJson($data);
        $url ="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
        $return = $this->requestAndCheck($url, 'POST', $post);
        if ($return === false) {
            return false;
        }

        return true;
    }

    /**
     * 指定一部分人群发消息，只有服务号可用
     * @param array|string $openids
     * @param $type string (text,image,voice,mpvideo,mpnews,wxcard)
     * @return boolean|array
     */
    public function sendMsgToMass($openids, $type, $content)
    {
        if (!$access_token = $this->getAccessToken()) {
            return false;
        }

        if (is_string($openids)) {
            $openids = explode(',', $openids);
        }
        $data = [
            'touser' => $openids,
            'msgtype' => $type,
        ];

        if ($type == 'text') {
            $data[$type]['content'] = $content; //text
        } elseif (in_array($type, ['image', 'voice'])) {
            $data[$type]['media_id'] = $content; //media_id
        } elseif ($type == 'mpnews') {
            $data[$type]['media_id'] = $content; //media_id
            $data[$type]['send_ignore_reprint'] = 1;//图文消息被判定为转载时，是否继续群发。 1为继续群发（转载），0为停止群发
        } elseif ($type == 'wxcard') {
            $data[$type]['card_id'] = $content; //card_id
        } elseif ($type == 'mpvideo') {
            //$content = {
            //    "media_id":"MEDIA_ID",
            //    "title":"TITLE",
            //    "thumb_media_id":"MEDIA_ID",
            //    "description":"DESCRIPTION"
            //}
            $data[$type] = $content;
        }

        $post = $this->toJson($data);
        $url ="https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token={$access_token}";
        $return = $this->requestAndCheck($url, 'POST', $post);
        if ($return === false) {
            return false;
        }

        return [
            'type' => $return['type'],//媒体文件类型，分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb），次数为news，即图文消息
            'msg_id' => $return['type'], //消息发送任务的ID
            'msg_data_id' => $return['msg_data_id'],//消息的数据ID,可以用于在图文分析数据接口中，获取到对应的图文消息的数据
        ];
    }

    /**
     * 给同一标签的所有粉丝发消息
     * @param int $tag_id 群发到的标签的tag_id, 0则表示发送给所有粉丝
     * @param $type string (text,image,voice,mpvideo,mpnews,wxcard)
     * @param mixed $content
     * @return boolean|array
     */
    public function sendMsgToGroup($tag_id, $type, $content)
    {
        if (!$access_token = $this->getAccessToken()) {
            return false;
        }

        $data = [
            'filter' => ['is_to_all' => !boolval($tag_id), 'tag_id' => $tag_id],
            'msgtype' => $type,
        ];

        if ($type == 'text') {
            $data[$type]['content'] = $content; //text
        } elseif (in_array($type, ['image', 'voice'])) {
            $data[$type]['media_id'] = $content; //media_id
        } elseif ($type == 'mpnews') {
            $data[$type]['media_id'] = $content; //media_id
            $data[$type]['send_ignore_reprint'] = 1;//图文消息被判定为转载时，是否继续群发。 1为继续群发（转载），0为停止群发
        } elseif ($type == 'wxcard') {
            $data[$type]['card_id'] = $content; //card_id
        } elseif ($type == 'mpvideo') {
            //$content = {
            //    "media_id":"MEDIA_ID",
            //    "title":"TITLE",
            //    "thumb_media_id":"MEDIA_ID",
            //    "description":"DESCRIPTION"
            //}
            $data[$type] = $content;
        }

        $post = $this->toJson($data);
        $url ="https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token={$access_token}";
        $return = $this->requestAndCheck($url, 'POST', $post);
        if ($return === false) {
            return false;
        }

        return [
            'type' => $return['type'],//媒体文件类型，分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb），次数为news，即图文消息
            'msg_id' => $return['type'], //消息发送任务的ID
            'msg_data_id' => $return['msg_data_id'],//消息的数据ID,可以用于在图文分析数据接口中，获取到对应的图文消息的数据
        ];
    }

    /**
     * 发送消息，自动识别id数
     * @param string|array $openids
     * @param $type string (text,image,voice,mpvideo,mpnews,wxcard)
     * @param mixed $content
     * @return boolean
     */
    public function sendMsg($openids, $type, $content)
    {
        if (empty($openids)) {
            return true;
        }
        if (is_string($openids)) {
            $openids = explode(',', $openids);
        }

        if (count($openids) > 1) {
            $result = $this->sendMsgToMass($openids, $type, $content);
        } else {
            $result = $this->sendMsgToOne($openids[0], $type, $content);
        }
        if ($result === false) {
            return false;
        }

        return true;
    }

    /**
     * 创建文本回复消息
     * @param string $fromUser
     * @param string $toUser
     * @param string $text
     * @return string
     */
    public function createReplyMsgOfText($fromUser, $toUser, $text)
    {
        $time = time();
        $template =
            "<xml>
            <ToUserName><![CDATA[$toUser]]></ToUserName>
            <FromUserName><![CDATA[$fromUser]]></FromUserName>
            <CreateTime>$time</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[$text]]></Content>
            </xml>";
        return $template;
    }

    /**
     * 创建图片回复消息
     * @param string $fromUser
     * @param string $toUser
     * @param string $mediaId
     * @return string
     */
    public function createReplyMsgOfImage($fromUser, $toUser, $mediaId)
    {
        $time = time();
        $template =
            "<xml>
            <ToUserName><![CDATA[$toUser]]></ToUserName>
            <FromUserName><![CDATA[$fromUser]]></FromUserName>
            <CreateTime>$time</CreateTime>
            <MsgType><![CDATA[image]]></MsgType>
            <Image>
            <MediaId><![CDATA[$mediaId]]></MediaId>
            </Image>
            </xml>";
        return $template;
    }

    /**
     * 创建图文回复消息
     * @param string $fromUser
     * @param string $toUser
     * @param array $articles
     * @return string
     */
    public function createReplyMsgOfNews($fromUser, $toUser, $articles)
    {
        $articles = array_slice($articles, 0, 7);//最多支持7个
        $num = count($articles);
        if (!$num) {
            return '';
        }

        $itemTpl = '';
        foreach ($articles as $item) {
            $itemTpl .=
                "<item>
            <Title><![CDATA[{$item['title']}]]></Title> 
            <Description><![CDATA[{$item['description']}]]></Description>
            <PicUrl><![CDATA[{$item['picurl']}]]></PicUrl>
            <Url><![CDATA[{$item['url']}]]></Url>
            </item>";
        }

        $time = time();
        $template =
            "<xml>
            <ToUserName><![CDATA[$toUser]]></ToUserName>
            <FromUserName><![CDATA[$fromUser]]></FromUserName>
            <CreateTime>$time</CreateTime>
            <MsgType><![CDATA[news]]></MsgType>
            <ArticleCount>$num</ArticleCount>
            <Articles>$itemTpl</Articles>
            </xml>";
        return $template;
    }
}
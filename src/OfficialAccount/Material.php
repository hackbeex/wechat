<?php
/**
 * Author: Hackbee
 */

namespace LightWechat\OfficialAccount;


trait Material
{
    /**
     * 新增媒质永久素材
     * 文档：https://mp.weixin.qq.com/wiki?action=doc&id=mp1444738729
     * @parem type $path 素材地址
     * @param string $type 类型有image,voice,video,thumb
     * @param array $param 目前是video类型需要
     * @return {"media_id":MEDIA_ID,"url":URL}
     */
    public function uploadMaterial($path, $type, $param=[])
    {
        if (!$access_token = $this->getAccessToken()) {
            return false;
        }

        $post_arr = ['media' => '@'.$path];
        if ($type == 'video') {
            $post_arr['description'] = $this->toJson([
                'title' => $param['title'],
                'introduction' => $param['introduction'],
            ]);
        }

        $url ="https://api.weixin.qq.com/cgi-bin/material/add_material?access_token={$access_token}&type={$type}";
        $return = $this->requestAndCheck($url, 'POST', $post_arr);
        if ($return === false) {
            return false;
        }

        return $return;
    }

    /**
     * 上传图文素材。 说明：news里面的图片只能用news_image，封面用image
     * 文档：https://mp.weixin.qq.com/wiki?action=doc&id=mp1444738729
     * @param array $articles
     *  [
     *      [
     *          "title"=> TITLE,
     *          "thumb_media_id"=> THUMB_MEDIA_ID, //封面图片素材id
     *          "author"=> AUTHOR,
     *          "digest"=> DIGEST, //图文消息的摘要，仅有单图文消息才有摘要，多图文此处为空。如果本字段为没有填写，则默认抓取正文前64个字。
     *          "show_cover_pic"=> SHOW_COVER_PIC(0 / 1), //是否显示封面，0为false，即不显示，1为true，即显示
     *          "content"=> CONTENT, //图文消息的具体内容，支持HTML标签，必须少于2万字符，小于1M，且此处会去除JS,
     *                                  涉及图片url必须来源"上传图文消息内的图片获取URL"接口获取。外部图片url将被过滤。
     *          "content_source_url"=> CONTENT_SOURCE_URL //图文消息的原文地址，即点击“阅读原文”后的URL
     *      ],
     *      //若新增的是多图文素材，则此处应还有几段articles结构(最多8段)
     *  ]
     * @return string|bool MEDIA_ID
     */
    public function uploadNews($articles)
    {
        if (!$access_token = $this->getAccessToken()) {
            return false;
        }

        $post = $this->toJson(["articles" => $articles]);
        $url ="https://api.weixin.qq.com/cgi-bin/material/add_news?access_token={$access_token}";
        $return = $this->requestAndCheck($url, 'POST', $post);
        if ($return === false) {
            return false;
        }

        return $return['media_id'];
    }

    /**
     * 上传图文消息中的图片
     * 文档：https://mp.weixin.qq.com/wiki?action=doc&id=mp1444738729
     * @param string $path 图片地址
     * @return string|bool 图片的url
     */
    public function uploadNewsImage($path)
    {
        if (!$access_token = $this->getAccessToken()) {
            return false;
        }

        $post_arr = ["media"=>'@'.$path];
        $url ="https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token={$access_token}";
        $return = $this->requestAndCheck($url, 'POST', $post_arr);
        if ($return === false) {
            return false;
        }

        return $return['url'];
    }

    /**
     * 上传临时材料（3天内有效）
     * 文档：https://mp.weixin.qq.com/wiki?action=doc&id=mp1444738726
     * @parem type $path 素材地址
     * @param string $type 类型有image,voice,video,thumb
     * @return array|bool {"type":"TYPE","media_id":"MEDIA_ID","created_at":123456789}
     */
    public function uploadTempMaterial($path, $type = 'image')
    {
        if (!($access_token = $this->getAccessToken())) {
            return false;
        }

        $post_arr = ['media' => '@'.$path];
        $url ="https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$access_token}&type={$type}";
        $return = $this->requestAndCheck($url, 'POST', $post_arr);
        if ($return === false) {
            return false;
        }

        return $return;
    }

    /**
     * 更新一篇图文
     * 文档：https://mp.weixin.qq.com/wiki?action=doc&id=mp1444738732&t=0.5904919423628598
     * @param string $mediaId MEDIA_ID
     * @param array $article INDEX
    {
    "title": TITLE,
    "thumb_media_id": THUMB_MEDIA_ID,
    "author": AUTHOR,
    "digest": DIGEST,
    "show_cover_pic": SHOW_COVER_PIC(0 / 1),
    "content": CONTENT,
    "content_source_url": CONTENT_SOURCE_URL
    }
     * @param number $index 要更新的文章在图文消息中的位置（多图文消息时，此字段才有意义），第一篇为0
     * @return boolean
     */
    public function updateNews($mediaId, $article, $index = 0)
    {
        if (!$access_token = $this->getAccessToken()) {
            return false;
        }

        $post = $this->toJson([
            'media_id' => $mediaId,
            'index' => $index,
            'articles' => $article
        ]);

        $url ="https://api.weixin.qq.com/cgi-bin/material/update_news?access_token={$access_token}";
        $return = $this->requestAndCheck($url, 'POST', $post);
        if ($return === false) {
            return false;
        }

        return true;
    }

    /**
     * 获取图文素材
     * @param string $mediaId
     * @return boolean|array
     */
    public function getNews($mediaId)
    {
        $wxdata = $this->getMaterial($mediaId);
        if ($wxdata === false) {
            return false;
        }

//    [
//        [
//        title 图文消息的标题
//        thumb_media_id	图文消息的封面图片素材id（必须是永久mediaID）
//        show_cover_pic	是否显示封面，0为false，即不显示，1为true，即显示
//        author	作者
//        digest	图文消息的摘要，仅有单图文消息才有摘要，多图文此处为空
//        content	图文消息的具体内容，支持HTML标签，必须少于2万字符，小于1M，且此处会去除JS
//        url	图文页的URL
//        content_source_url	图文消息的原文地址，即点击“阅读原文”后的URL
//        ],
//        //多图文消息有多篇文章
//     ]
        return $wxdata['news_item'];
    }

    /**
     * 获取媒质素材
     * @param string $mediaId
     * @return boolean
    array video返回{
    "title":TITLE,
    "description":DESCRIPTION,
    "down_url":DOWN_URL,
    }
     */
    public function getMaterial($mediaId)
    {
        if (!$access_token = $this->getAccessToken()) {
            return false;
        }

        $post = $this->toJson(['media_id' => $mediaId]);
        $url ="https://api.weixin.qq.com/cgi-bin/material/get_material?access_token={$access_token}";
        $return = $this->requestAndCheck($url, 'POST', $post);
        if ($return === false) {
            return false;
        }
        return true;
    }

    /**
     * 删除素材，包括图文
     * @param string $mediaId
     * @return boolean
     */
    public function delMaterial($mediaId)
    {
        if (!$access_token = $this->getAccessToken()) {
            return false;
        }

        $post = $this->toJson(['media_id' => $mediaId]);
        $url ="https://api.weixin.qq.com/cgi-bin/material/del_material?access_token={$access_token}";
        $return = $this->requestAndCheck($url, 'POST', $post);
        if ($return === false) {
            return false;
        }

        return true;
    }

    /**
     * 获取素材总数
     * @return array|bool
    //voice_count	语音总数量
    //video_count	视频总数量
    //image_count	图片总数量
    //news_count	图文总数量
     */
    public function getMaterialCount()
    {
        if (!$access_token = $this->getAccessToken()) {
            return false;
        }

        $url ="https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token={$access_token}";
        $return = $this->requestAndCheck($url, 'GET');
        if ($return === false) {
            return false;
        }

        return $return;
    }

    /**
     * 获取素材列表
     * @param string $type 素材的类型，图片（image）、视频（video）、语音 （voice）、图文（news）
     * @param int $offset 从全部素材的该偏移位置开始返回，0表示从第一个素材 返回
     * @param int $count 返回素材的数量，取值在1到20之间
     * @return array|bool
     */
    public function getMaterialList($type, $offset, $count)
    {
        if (!$access_token = $this->getAccessToken()) {
            return false;
        }

        $post = $this->toJson([
            'type' => $type,
            'offset' => $offset,
            'count' => $count
        ]);

        $url ="https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token={$access_token}";
        $return = $this->requestAndCheck($url, 'POST', $post);
        if ($return === false) {
            return false;
        }

        /* 返回图文消息结构 */
        //{
        //  "total_count": TOTAL_COUNT,
        //  "item_count": ITEM_COUNT,
        //  "item": [{
        //      "media_id": MEDIA_ID,
        //      "content": {
        //          "news_item": [{
        //              "title": TITLE,
        //              "thumb_media_id": THUMB_MEDIA_ID,
        //              "show_cover_pic": SHOW_COVER_PIC(0 / 1),
        //              "author": AUTHOR,
        //              "digest": DIGEST,
        //              "content": CONTENT,
        //              "url": URL,
        //              "content_source_url": CONTETN_SOURCE_URL
        //          },
        //          //多图文消息会在此处有多篇文章
        //          ]
        //       },
        //       "update_time": UPDATE_TIME
        //   },
        //   //可能有多个图文消息item结构
        // ]
        //}

        /*其他类型*/
        //{
        //  "total_count": TOTAL_COUNT,
        //  "item_count": ITEM_COUNT,
        //  "item": [{
        //      "media_id": MEDIA_ID,
        //      "name": NAME,
        //      "update_time": UPDATE_TIME,
        //      "url":URL
        //  },
        //  //可能会有多个素材
        //  ]
        //}
        return $return;
    }
}
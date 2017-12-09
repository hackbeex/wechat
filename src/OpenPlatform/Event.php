<?php
/**
 * Author: Hackbee
 */

namespace LightWechat\OpenPlatform;

use LightWechat\Utils\XML;
use LightWechat\Utils\MsgCrypt\WXBizMsgCrypt;

trait Event
{
    /**
     * 获取推送解密的消息
     * @return mixed 消息数组
     */
    public function getDecryptPushMessage()
    {
        $content = $GLOBALS['HTTP_RAW_POST_DATA'] ?: file_get_contents('php://input');
        if (empty($content)) {
            $this->setError('推送消息为空！');
            return false;
        }

        $this->logDebugFile($content);

        $decryptMsg = $this->decryptPushMsg($content);

        $this->logDebugFile($decryptMsg);

        $message = XML::parse($decryptMsg);
        if (empty($message)) {
            $this->setError('推送消息内容为空！');
            return false;
        }
        $this->logDebugFile($message);

        return $message;
    }

    /**
     * 解密推送消息
     * @param string $encryptMsg
     */
    public function decryptPushMsg($encryptMsg)
    {
        $xmlTree = new \DOMDocument();
        $xmlTree->loadXML($encryptMsg);
        $arrayEnc = $xmlTree->getElementsByTagName('Encrypt');
        $encrypt = $arrayEnc->item(0)->nodeValue;

        $format = '<xml><AppId><![CDATA[%s]]></AppId><Encrypt><![CDATA[%s]]></Encrypt></xml>';
        $fromXml = sprintf($format, $this->options['appid'], $encrypt);

        $msgSignature = $_GET['msg_signature'];
        $timeStamp = $_GET['timestamp'];
        $nonce = $_GET['nonce'];
        $this->logDebugFile(compact('msgSignature', 'timeStamp', 'nonce'));

        $wxBizMsgCrypt = new WXBizMsgCrypt($this->options['verify_token'], $this->options['encoding_aes_key'], $this->options['appid']);
        $errCode = $wxBizMsgCrypt->decryptMsg($msgSignature, $timeStamp, $nonce, $fromXml, $msg);
        if ($errCode != 0) {
            $this->logDebugFile('解密错误码：'.$errCode);
            $this->setError('解密错误码：'.$errCode);
            return false;
        }

        return $msg;
    }

    /**
     * 获取普通推送消息
     * @return array|bool|\SimpleXMLElement
     */
    public function getPushMessage()
    {
        $content = $GLOBALS['HTTP_RAW_POST_DATA'] ?: file_get_contents('php://input');
        if (empty($content)) {
            $this->setError('推送消息为空！');
            return false;
        }

        $this->logDebugFile($content);

        $message = XML::parse($content);
        if (empty($message)) {
            $this->setError('推送消息内容为空！');
            return false;
        }
        $this->logDebugFile($message);

        return $message;
    }
}
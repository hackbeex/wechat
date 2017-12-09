<?php
/**
 * Author: Hackbee
 */

namespace LightWechat\Core;

use LightWechat\Utils\Request;
use LightWechat\Utils\FileCache;


class WxCommon
{
    /**
     * @var string 错误字符串信息
     */
    protected $errorMsg = '微信默认错误信息';

    /**
     * @var bool 是否开启调试
     */
    protected $debug = false;

    /**
     * @var bool 是否记录错误到文本
     */
    protected $isLogFile = false;

    /**
     * @var string 记录错误的文本路径
     */
    protected $logFile = "./wechat-debug.log";

    /**
     * @var Request http请求类
     */
    protected $request;

    /**
     * 缓存数据
     * @var FileCache
     */
    protected $cache;


    protected function __construct()
    {
        $this->request = new Request;

        $this->cache = new FileCache('light-wechat.cache', get_called_class());
    }

    /**
     * @return FileCache
     */
    protected function getCache()
    {
        return $this->cache;
    }

    public function getError() 
    {
        return $this->errorMsg;
    }
    
    protected function setError($error)
    {
        if (!is_string($error)) {
            $error = json_encode($error, JSON_UNESCAPED_UNICODE);
        }
        $this->errorMsg = $error;
    }
    
    public function isDedug()
    {
        return $this->debug;
    }

    public function setLogFile($logFile)
    {
        $this->logFile = $logFile;
    }
    
    public function logDebugFile($content)
    {
        if (!$this->isLogFile) {
            return;
        }
        if (!is_string($content)) {
            $encode = json_encode($content, JSON_UNESCAPED_UNICODE);
            $encode && $content = $encode;
        }
        file_put_contents($this->logFile, date('Y-m-d H:i:s').' -- '.$content."\n", FILE_APPEND);
    }

    /**
     * 请求并对结果进行初步检查
     * @param string $url 请求的url
     * @param string $method 请求的方式
     * @param array|string $fields 请求的值
     * @param bool $result_json 是否对结果进行json处理
     * @return bool|mixed|string
     */
    protected function requestAndCheck($url, $method = 'GET', $fields = [], $result_json = true)
    {
        $return = $this->request->httpRequest($url, $method, $fields);
        if ($return === false) {
            $this->setError("http请求出错！");
            return false;
        }

        $wxdata = json_decode($return, true);
        if (!$result_json && $wxdata === null) {
            return $return; //不能解码，如图片
        }

        $this->logDebugFile(['url' => $url,'fields' => $fields,'wxdata' => $wxdata]);
        if (isset($wxdata['errcode']) && $wxdata['errcode'] != 0) {
            $errmsg = WxCode::getItem($wxdata['errcode']);
            if ($this->debug) {
                $this->setError("微信错误码：{$wxdata['errcode']};<br>中文信息：$errmsg<br>原信息：{$wxdata['errmsg']}<br>链接：$url");
            } else {
                if ($errmsg === false) {
                    $errmsg = $wxdata['errmsg'];
                    if (($pos = strpos($errmsg, ' hint')) > 0) {
                        $errmsg = substr($errmsg, 0, $pos);
                    }
                }
                $this->setError("微信提醒：{$errmsg}[{$wxdata['errcode']}]");
            }
            return false;
        }

        if (strtoupper($method) === 'GET' && empty($wxdata)) {
            if ($this->debug) {
                $this->setError("微信http请求返回为空！请求链接：$url");
            } else {
                $this->setError("微信http请求返回为空！操作失败");
            }
            return false;
        }

        return $wxdata;
    }
    
    public function toJson($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
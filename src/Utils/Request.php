<?php
/**
 * Author: Hackbee
 */

namespace LightWechat\Utils;


class Request
{
    /**
     * http请求
     * @param string $url
     * @param string $method
     * @param string|array $fields
     * @return string
     */
    public function httpRequest($url, $method = 'GET', $fields = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $method = strtoupper($method);
        if ($method == 'GET' && !empty($fields)) {
            is_array($fields) && $fields = http_build_query($fields);
            $url = $url . (strpos($url,"?")===false ? "?" : "&") . $fields;
        }
        curl_setopt($ch, CURLOPT_URL, $url);

        if ($method != 'GET') {
            $hadFile = false;
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($fields)) {
                if (is_array($fields)) {
                    /* 支持文件上传 */
                    if (class_exists('\CURLFile')) {
                        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
                        foreach ($fields as $key => $value) {
                            if ($this->isPostHasFile($value)) {
                                $fields[$key] = new \CURLFile(realpath(ltrim($value, '@')));
                                $hadFile = true;
                            }
                        }
                    } elseif (defined('CURLOPT_SAFE_UPLOAD')) {
                        foreach ($fields as $key => $value) {
                            if ($this->isPostHasFile($value)) {
                                curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
                                $hadFile = true;
                                break;
                            }
                        }
                    }
                }
                $fields = (!$hadFile && is_array($fields)) ? http_build_query($fields) : $fields;
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            }
        }

        /* 关闭https验证 */
        if ("https" == substr($url, 0, 5)) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $content = curl_exec($ch);
        curl_close($ch);

        return $content;
    }

    private function isPostHasFile($value)
    {
        if (is_string($value) && strpos($value, '@') === 0 && is_file(realpath(ltrim($value, '@')))) {
            return true;
        }
        return false;
    }
}
<?php
/**
 * Author: Hackbee
 */

namespace LightWechat\Utils;


class FileCache
{
    /**
     * @var string 缓存的文件路径
     */
    private $filepath;

    private $prefix;

    /**
     * @var null|array 缓存的数据集
     */
    private $data = null;


    public function __construct($filepath = 'cache', $prefix = '')
    {
        $this->filepath = $filepath;

        $this->prefix = $prefix;

        if ( ! file_exists($this->filepath)) {
            mkdir($this->filepath, 0777, true);
        }
    }


    public function get($key)
    {
        if ($this->data === null) {
            $this->data = unserialize(file_get_contents($this->filepath));
        }

        $key = $this->getWholeKey($key);

        if (isset($this->data[$key])) {
            if ($this->data[$key]['expires'] === 0 || $this->data[$key]['expires'] > time()) {
                return $this->data[$key]['value'];
            }

            unset($this->data[$key]);
            file_put_contents($this->filepath, serialize($this->data));
        }

        return null;
    }


    public function set($key, $value, $expireSeconds = 0)
    {
        if ($this->data === null) {
            $this->data = unserialize(file_get_contents($this->filepath));
        }

        if (empty($this->data)) {
            $this->data = [];
        }

        $key = $this->getWholeKey($key);
        $this->data[$key] = [
            'value'     => $value,
            'expires'   => $expireSeconds + time(),
        ];

        file_put_contents($this->filepath, serialize($this->data));
    }


    public function delete($key)
    {
        if ($this->data === null) {
            $this->data = unserialize(file_get_contents($this->filepath));
        }

        $key = $this->getWholeKey($key);

        if (isset($this->data[$key])) {
            unset($this->data[$key]);
            file_put_contents($this->filepath, serialize($this->data));
        }
    }


    private function getWholeKey($key)
    {
        return $this->prefix.'::'.$key;
    }
}
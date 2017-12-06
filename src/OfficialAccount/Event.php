<?php
/**
 * Author: Hackbee
 */

namespace LightWechat\OfficialAccount;

use LightWechat\Utils\XML;

trait Event
{
    protected $events = [];       // 注册的事件

    /**
     * 订阅消息事件
     * @param $event_type
     * @param $callback
     */
    public function registerMsgEvent($event_type, $callback)
    {
        $this->events[$event_type] = $callback;
    }

    /**
     * 处理消息事件
     */
    public function handleMsgEvent()
    {
        $msg = $this->getPushMessage();
        if ( ! $msg) {
            exit($this->getError());
        }

        // 先处理全局事件
        if (isset($this->events[self::EVENT_ALL]) && is_callable($this->events[self::EVENT_ALL])) {
            $this->events[self::EVENT_ALL]($msg);
        }

        $event_parse = [
            self::EVENT_TEXT        => ['MsgType' => 'text'],
            self::EVENT_SUBSCRIBE   => ['MsgType' => 'event', 'Event' => 'subscribe'],
            self::EVENT_UNSUBSCRIBE => ['MsgType' => 'event', 'Event' => 'unsubscribe'],
            self::EVENT_SCAN        => ['MsgType' => 'event', 'Event' => 'SCAN'],
            self::EVENT_LOCATION    => ['MsgType' => 'event', 'Event' => 'LOCATION'],
            self::EVENT_CLICK       => ['MsgType' => 'event', 'Event' => 'CLICK'],
            self::EVENT_VIEW        => ['MsgType' => 'event', 'Event' => 'VIEW'],
        ];

        // 找出注册的事件并处理
        foreach ($this->events as $event => $callback) {
            if ( ! isset($event_parse[$event])) {
                continue;
            }

            $find_event = true;
            foreach ($event_parse[$event] as $key => $word) {
                if ($msg[$key] !== $word) {
                    $find_event = false;
                    break;
                }
            }
            if ( ! $find_event) {
                continue;
            }

            is_callable($callback) && $callback($msg);
            break;
        }
    }

    /**
     * 推送消息处理接口
     * @return array|bool
     */
    public function getPushMessage()
    {
        $content = $GLOBALS['HTTP_RAW_POST_DATA'] ?: file_get_contents('php://input');

        $this->logDebugFile($content);

        $message = XML::parse($content);
        if (empty($message)) {
            $this->setError('推送消息为空！');
            return false;
        }

        $this->logDebugFile($message);

        return $message;
    }
}
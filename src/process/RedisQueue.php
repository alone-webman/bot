<?php

namespace AloneWebMan\Bot\process;

use AloneWebMan\Bot\Facade;

/**
 * 获取redis队列信息处理
 */
class RedisQueue extends Common {
    public function onWorkerStart(mixed $worker): void {
        $this->getPluginName($worker);
        $config = Facade::config($this->plugin_name);
        Facade::timer($config['queue_timer'], function() use ($config) {
            alone_redis_get($config['queue_redis_key'], $config['queue_task'], function($arr) {
                if (!empty($plugin = $arr['plugin'] ?? '') && !empty($token = $arr['token'] ?? '') && !empty($post = $arr['post'] ?? [])) {
                    Facade::exec($plugin, $token, $post);
                }
            });
        });
    }
}